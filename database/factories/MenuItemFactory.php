<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        $categories = Category::pluck('id_cate')->toArray();

        $foodNames = [
            'Ba chỉ nướng',
            'Sườn nướng',
            'Thịt bò wagyu',
            'Thịt heo cuốn lá lốt',
            'Gà nướng từ thiện',
            'Cá hồi nướng',
            'Tôm nướng',
            'Mực nướng',
            'Bạch tuộc nướng',
            'Rau muống xào',
            'Đậu hũ chiên',
            'Salad trộn',
            'Soup gà',
            'Canh chua cá',
            'Cơm trắng',
            'Cơm rang',
            'Mì xào',
            'Bánh mì kẹp thịt',
            'Nước suối',
            'Coca Cola',
            'Pepsi',
            'Nước cam',
            'Sinh tố bơ',
            'Trà đá',
        ];

        return [
            'name' => $this->faker->randomElement($foodNames),
            'description' => $this->faker->sentence(10),
            'price' => $this->faker->numberBetween(15000, 500000),
            'status' => $this->faker->randomElement([0, 1]),
            'id_cate' => !empty($categories) ? $this->faker->randomElement($categories) : null,
            'image' => 'food' . $this->faker->numberBetween(1, 10) . '.jpeg',
        ];
    }
}