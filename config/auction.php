<?php

return [
    'anti_sniping_extension_seconds' => (int) env('AUCTION_ANTI_SNIPING_EXTENSION', 10),
    'anti_sniping_window_seconds' => (int) env('AUCTION_ANTI_SNIPING_WINDOW', 5),
    'projector_refresh_seconds' => (int) env('AUCTION_PROJECTOR_REFRESH', 1),
];
