<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;

class ReminderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
            $user_ids = User::pluck('id')->toArray();

            return [
            'user_id'=>$this->faker->randomElement($user_ids),
            'title' => $this->faker->name(),
            'description'=>$this->faker->text(),
            'is_done'=>$this->faker->randomElement([true,false]),

        ];
    }
}
