<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TourListTest extends TestCase
{
    use RefreshDatabase;

    public function test_tours_list_by_travel_slug_returns_correct_tours()
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create(['travel_id' => $travel->id]);
        $response = $this->get('api/v1/travels/' . $travel->slug . '/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $tour->id]);
    }

    public function test_tour_price_is_shown_correctly(): void
    {
        $travel = Travel::Factory()->create();
        Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 123.45,
        ]);
        $response = $this->get('api/v1/travels/' . $travel->slug . '/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');

        $response->assertJsonFragment(['price' => '123.45']);
    }

    public function test_tours_list_returns_pagination()
    {
        $travel = Travel::factory()->create();
        Tour::factory(16)->create(['travel_id' => $travel->id]);
        $response = $this->get('api/v1/travels/' . $travel->slug . '/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(15, 'data');
        $response->assertJsonPath('meta.last_page', 2);
    }

    public function test_tours_list_sorts_by_starting_date_correctly()
    {
        $travel = Travel::factory()->create();

        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);

        $earlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now(),
            'ending_date' => now()->addDays(1),
        ]);

        $response = $this->getJson('api/v1/travels/' . $travel->slug . '/tours');

        $response->assertStatus(200);

        // Make sure order is correct
        $response->assertJsonPath('data.0.id', $earlierTour->id);
        $response->assertJsonPath('data.1.id', $laterTour->id);
    }

    public function test_tours_list_sorts_by_price_correctly()
    {
        $travel = Travel::factory()->create();

        $expensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 500,
        ]);

        $cheapLaterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);

        $cheapEarlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200,
            'starting_date' => now(),
            'ending_date' => now()->addDays(1),
        ]);

        $response = $this->getJson('api/v1/travels/' . $travel->slug . '/tours?sortBy=price&sortOrder=asc');

        $response->assertStatus(200);

        // Assert sorted by price (100, 200, 500)
        $response->assertJsonPath('data.0.id', $cheapLaterTour->id);
        $response->assertJsonPath('data.1.id', $cheapEarlierTour->id);
        $response->assertJsonPath('data.2.id', $expensiveTour->id);
    }

    public function test_tours_list_filters_by_price_correctly()
    {
        $travel = Travel::factory()->create();

        $expensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price'     => 200,
        ]);

        $cheapTour = Tour::factory()->create([
            'travel_id'     => $travel->id,
            'price'         => 100,
            'starting_date' => now()->addDays(2),
            'ending_date'   => now()->addDays(3),
        ]);

        $endPoint = 'api/v1/travels/' . $travel->slug . '/tours';

        // Case 1: priceFrom = 100 → both tours
        $response = $this->getJson($endPoint . '?priceFrom=100');
        $response->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $cheapTour->id])
            ->assertJsonFragment(['id' => $expensiveTour->id]);

        // Case 2: priceFrom = 150 → only expensive tour
        $response = $this->getJson($endPoint . '?priceFrom=150');
        $response->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $expensiveTour->id]);

        // Case 3: priceFrom = 200 → only expensive tour
        $response = $this->getJson($endPoint . '?priceFrom=200');
        $response->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $expensiveTour->id]);

        // Case 4: priceFrom = 50 & priceTo = 150 → only cheap tour
        $response = $this->getJson($endPoint . '?priceFrom=50&priceTo=150');
        $response->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $cheapTour->id]);

        // Case 5: priceFrom = 201 → no tours
        $response = $this->getJson($endPoint . '?priceFrom=201');
        $response->assertJsonCount(0, 'data');

        // Case 6: priceFrom = 150 & priceTo = 250 → only expensive tour
        $response = $this->getJson($endPoint . '?priceFrom=150&priceTo=250');
        $response->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $expensiveTour->id]);
    }

    public function test_tours_list_filters_by_starting_date_correctly()
    {
        $travel = Travel::factory()->create();

        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2)->toDateString(),
            'ending_date' => now()->addDays(3)->toDateString(),
        ]);

        $earlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->toDateString(),
            'ending_date' => now()->addDays(1)->toDateString(),
        ]);

        $endPoint = 'api/v1/travels/' . $travel->slug . '/tours';

        // Case 1: no filter → expect 2 tours
        $response = $this->get($endPoint . '?dateFrom=' . now()->toDateString());
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $earlierTour->id]);
        $response->assertJsonFragment(['id' => $laterTour->id]);

        // Case 2: filter from tomorrow → expect only laterTour
        $response = $this->get($endPoint . '?dateFrom=' . now()->addDay()->toDateString());
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $laterTour->id]);

        // Case 3: filter far future → expect none
        $response = $this->get($endPoint . '?dateFrom=' . now()->addDays(5)->toDateString());
        $response->assertJsonCount(0, 'data');

        // Case 4: filter with date range → only laterTour should match
        /*$response = $this->get($endPoint . '?dateFrom=' . now()->addDay()->toDateString() . '&dateTo=' . now()->addDays(5)->toDateString());
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $laterTour->id]);*/
    }


    public function test_tours_list_returns_validations_errors()
    {
        $travel = Travel::factory()->create();

        $endPoint = 'api/v1/travels/' . $travel->slug . '/tours';

        $response = $this->getJson($endPoint . '?dateFrom=abcd');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['dateFrom']);

        // Invalid price
        $response = $this->getJson($endPoint . '?priceFrom=abcd');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['priceFrom']);
    }
}
