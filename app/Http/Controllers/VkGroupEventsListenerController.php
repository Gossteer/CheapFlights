<?php

namespace App\Http\Controllers;

use App\Services\VkGroupEventsListener;

//Скорее всего больше не используются
class VkGroupEventsListenerController extends Controller
{
    public function bot()
    {
        if (!isset($_REQUEST)) {
            exit;
        }

        $listener = new VkGroupEventsListener();
        $listener->recognizeEvent();

        vkResponse('ok');
    }
}
