<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\UserChat;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

//Больше не используется
class GroupController extends Controller
{
    public function save(Request $request): JsonResponse
    {
        $userId = $request->post('user_id');
        $appId = $request->post('app_id');
        $value = $request->post('checkbox');
        $user = User::firstOrNew(['id' => $userId]);
        $user->id = $userId;
        $user->save();

        $group = Group::where(['number' => $value[0]])->first() ?? new Group();
        $group->number = $value[0];
        $group->save();
        $user->groups()->save($group);

        return response()->json([
            'response' => '/?vk_user_id=' . $userId . '&vk_app_id=' . $appId
        ]);
    }

    public function list(Request $request)
    {
        $sex = (int) $request->get('sex');

        if ($sex === 2) {
            return [
                198318499,
                198320103,
                198320123,
                198320155,
                198320168,
                198320191,
                198320202,
                198320213,
                198320223,
                198320457,
                198320488,
                198320507,
                198320520,
                198320542,
                198320558,
                198320570,
                198320588,
                198320609,
                198320625,
                198320640,
            ];
        } elseif ($sex === 1) {
            return [
                198385612,
                198385665,
                198385715,
                198385755,
                198385816,
                198385850,
                198385865,
                198385889,
                198385909,
                198385937,
                198385950,
                198385973,
                198385997,
                198386060,
                198386129,
                198386198,
                198386291,
                198386334,
                198386383,
                198386441,
            ];
        } else {
            return [
                198385937,
                198320588,
                198386441,
                198320558,
                198386383,
                198320609,
                198386060,
                198320507,
                198385909,
                198320488,
                198385937,
                198320457,
                198385889,
                198320223,
                198385865,
                198320213,
                198385755,
                198320155,
                198385715,
                198320123,
            ];
        }
    }
}
