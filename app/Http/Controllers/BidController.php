<?php

namespace App\Http\Controllers;

use App\Exceptions\AuctionException;
use App\Http\Requests\PlaceBidRequest;
use App\Services\AuctionEngine;
use Illuminate\Http\JsonResponse;

class BidController extends Controller
{
    public function __invoke(PlaceBidRequest $request, AuctionEngine $auctionEngine): JsonResponse
    {
        try {
            $bid = $auctionEngine->placeBid(
                teamId: (int) $request->integer('team_id'),
                playerId: (int) $request->integer('player_id'),
                isAutoBid: (bool) $request->boolean('is_auto_bid', false),
            );
        } catch (AuctionException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Bid placed successfully.',
            'bid_id' => $bid->id,
            'amount' => $bid->amount,
        ]);
    }
}
