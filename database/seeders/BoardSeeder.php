<?php

namespace Database\Seeders;

use App\Logic\BoardLogic;
use App\Models\Board;
use App\Models\Column;
use App\Models\Team;
use App\Models\Card;
use App\Models\TeamBoard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $boardList = ["Development", "Testing", "Design"];

        $team = Team::where("name", "Taskly")->first();
        $board =  Board::create([
            "team_id" => $team->id,
            "name" => "Developemnt",
            "pattern" => BoardLogic::PATTERN[array_rand(BoardLogic::PATTERN)],
        ]);

        $col1 = Column::create([
            "name" => "To-Do",
            "board_id" => $board->id,
        ]);

        $card1 = Card::create([
            "name" => "Create Login View",
            "column_id" => $col1->id,
        ]);

        $card2 = Card::create([
            "name" => "Create Registration View",
            "column_id" => $col1->id,
        ]);

        $card3 = Card::create([
            "name" => "Create Home View",
            "column_id" => $col1->id,
        ]);

        $card1->next_id = $card2->id;
        $card2->previous_id = $card1->id;
        $card2->next_id = $card3->id;
        $card3->previous_id = $card2->id;
        $card1->save();
        $card2->save();
        $card3->save();
    }
}
