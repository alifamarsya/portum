<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        $date = now()->subDays(rand(0, 30));

        return [
            'ticket_number'         => 'REQ-' . $date->format('Ymd') . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
            'user_id'               => 1,
            'department_id'         => rand(1, 3),
            'category_id'           => null,
            'assigned_to'           => null,
            'disposed_by'           => null,
            'priority'              => fake()->randomElement(['Rendah', 'Sedang', 'Tinggi']),
            'status'                => 'Menunggu Verifikasi',
            'description'           => fake()->sentence(8),
            'disposition_notes'     => null,
            'disposed_at'           => null,
            'attachment_path'       => null,
            'sla_response_start_at' => null,
            'sla_response_due_at'   => null,
            'verified_at'           => null,
            'verified_by'           => null,
            'sla_response_time_minutes' => null,
            'sla_response_status'   => null,
            'sla_resolution_hours'  => null,
            'sla_resolution_start_at' => null,
            'sla_resolution_due_at' => null,
            'resolved_at'           => null,
            'sla_resolution_time_minutes' => null,
            'sla_resolution_status' => null,
            'completed_at'          => null,
            'confirmation_deadline' => null,
            'closed_at'             => null,
            'created_at'            => $date,
            'updated_at'            => $date,
        ];
    }
}
