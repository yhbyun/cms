<?php

namespace Tests\Preferences;

use Tests\TestCase;
use Statamic\API\User;
use Statamic\API\Preference;
use Tests\PreventSavingStacheItemsToDisk;

class EndpointsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_can_set_a_preference()
    {
        $user = User::make()->makeSuper()->save();

        $expected = [
            'favorites' => [
                'foods' => [
                    'pizza',
                    'lasagna'
                ]
            ]
        ];

        $response = $this
            ->actingAs($user)
            ->post(cp_route('preferences.store'), [
                'key' => 'favorites.foods',
                'value' => [
                    'pizza',
                    'lasagna'
                ]
            ])
            ->assertExactJson($expected);

        $this->assertEquals($expected, Preference::all());
    }

    /** @test */
    function it_can_append_a_preference()
    {
        $user = User::make()
            ->makeSuper()
            ->setPreference('favorites.foods', ['pizza', 'lasagna'])
            ->save();

        $expected = [
            'favorites' => [
                'foods' => [
                    'pizza',
                    'lasagna',
                    'spaghetti'
                ]
            ]
        ];

        $response = $this
            ->actingAs($user)
            ->post(cp_route('preferences.store'), [
                'key' => 'favorites.foods',
                'value' => 'spaghetti',
                'append' => true
            ])
            ->assertExactJson($expected);

        $this->assertEquals($expected, Preference::all());
    }

    /** @test */
    function it_can_remove_a_preference()
    {
        $user = User::make()
            ->makeSuper()
            ->setPreferences([
                'food' => 'pizza',
                'color' => 'red'
            ])
            ->save();

        $expected = [
            'color' => 'red'
        ];

        $response = $this
            ->actingAs($user)
            ->delete(cp_route('preferences.destroy', 'food'))
            ->assertExactJson($expected);

        $this->assertEquals($expected, Preference::all());
    }

    /** @test */
    function it_can_remove_a_preference_array_value()
    {
        $user = User::make()
            ->makeSuper()
            ->setPreferences([
                'favorites' => [
                    'foods' => [
                        'pizza',
                        'lasagna',
                        'spaghetti'
                    ]
                ]
            ])
            ->save();

        $expected = [
            'favorites' => [
                'foods' => [
                    'pizza',
                    'spaghetti'
                ]
            ]
        ];

        $response = $this
            ->actingAs($user)
            ->delete(cp_route('preferences.destroy', 'favorites.foods'), ['value' => 'lasagna'])
            ->assertExactJson($expected);

        $this->assertEquals($expected, Preference::all());
    }
}