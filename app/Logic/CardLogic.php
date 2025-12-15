<?php

namespace App\Logic;

use App\Models\Card;
use App\Models\CardHistory;
use App\Models\CardUser;

class CardLogic
{
    public function getData(int $card_id)
    {
        $card = Card::find($card_id);
        return $card;
    }

    public function getWorkers(int $card_id)
    {
        $users = Card::find($card_id)->users()->get();
        return $users;
    }

    public function addUser(int $card_id, int $user_id)
    {
        CardUser::create([
            "user_id" => $user_id,
            "card_id" => $card_id,
        ]);
        return;
    }

    public function removeUser(int $card_id, int $user_id)
    {
        CardUser::where([
            "user_id" => $user_id,
            "card_id" => $card_id,
        ])->delete();
        return;
    }

    function cardAddEvent(int $card_id, int $user_id, string $content)
    {
        $event = CardHistory::create([
            "user_id" => $user_id,
            "card_id" => $card_id,
            "type" => "event",
            "content" => $content,
        ]);

        return $event;
    }

    function cardComment(int $card_id, int $user_id, string $content)
    {
        $event = CardHistory::create([
            "user_id" => $user_id,
            "card_id" => $card_id,
            "type" => "comment",
            "content" => $content,
        ]);

        return $event;
    }

    function getHistories(int $card_id)
    {
        $evets = CardHistory::with("user")
            ->where("card_id", $card_id)
            ->orderBy("created_at")
            ->get();
        return $evets;
    }

    function archiveCard(int $target_card_id)
    {
        $target_card = Card::find($target_card_id);
        $top_card = null;
        $bottom_card = null;
        if (!$target_card) return;
        if ($target_card->previous_id) $top_card = Card::find($target_card->previous_id);
        if ($target_card->next_id) $bottom_card = Card::find($target_card->next_id);

        if ($top_card) {
            $top_card->next_id = $bottom_card ? $bottom_card->id : null;
            $top_card->save();
        }
        if ($bottom_card) {
            $bottom_card->previous_id = $top_card ? $top_card->id : null;
            $bottom_card->save();
        }
        $target_card->archive = true;
        $target_card->archived_at = now();
        $target_card->save();
    }

    function unarchiveCard(int $target_card_id)
    {
        $target_card = Card::find($target_card_id);
        if (!$target_card) return;

        // Find the last card in the column to append the unarchived card
        $last_card = Card::where('column_id', $target_card->column_id)
            ->where('id', '!=', $target_card->id)
            ->where('archive', false)
            ->whereNull('next_id')
            ->first();

        if ($last_card) {
            $last_card->next_id = $target_card->id;
            $target_card->previous_id = $last_card->id;
            $target_card->next_id = null;
            $last_card->save();
        } else {
            // If no other cards in the list, just reset the pointers
            $target_card->previous_id = null;
            $target_card->next_id = null;
        }

        $target_card->archive = false;
        $target_card->archived_at = null;
        $target_card->save();

        return $target_card;
    }

    function deleteCard(int $target_card_id)
    {
        $target_card = Card::find($target_card_id);
        $top_card = null;
        $bottom_card = null;
        if (!$target_card) return;
        if ($target_card->previous_id) $top_card = Card::find($target_card->previous_id);
        if ($target_card->next_id) $bottom_card = Card::find($target_card->next_id);

        if ($top_card) {
            $top_card->next_id = $bottom_card ? $bottom_card->id : null;
            $top_card->save();
        }
        if ($bottom_card) {
            $bottom_card->previous_id = $top_card ? $top_card->id : null;
            $bottom_card->save();
        }
        $target_card->delete();
    }
}
