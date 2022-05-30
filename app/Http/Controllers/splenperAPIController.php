<?php

namespace App\Http\Controllers;

use App\Models\ApiAccount;
use Illuminate\Support\Facades\Http;

class splenperAPIController extends Controller
{
    public function splenperAPI()
    {
        $maxId = '';
        $users = [
            'sessionid=; csrftoken=; ds_user_id=; ig_did=; ig_nrcb=1; mid=; rur=;
shbid=; shbts=',
            'sessionid=; csrftoken=; ds_user_id=; ig_did=; ig_nrcb=1; mid=; rur=;
shbid=; shbts=',
        ];
        $response = Http::withHeaders([
            'cookie' => $users[1],
            'x-ig-app-id' => '936619743392459',
            'Content-Type' => 'application/json',
        ])->get('https://www.instagram.com/zehrasena_/?__a=1');
        $response = $response->json();
        $userId = $response['graphql']['user']['id'];
        $biography = $response['graphql']['user']['biography'];
        $followersCount = $response['graphql']['user']['edge_followed_by']['count'];
        $count = 12;

        $index = 0;
        $isMoreAvailable = true;

        $totalLikeCount = 0;
        $totalCommentCount = 0;

        while ($index < $count && $isMoreAvailable) {
            $variables = json_encode([
                'id' => $userId,
                "after" => $maxId,
                "first" => $count,
            ]);

            $variables = urlencode($variables);
            if ($index % 2 == 0) {
                $userIndex = 0;
            } else {
                $userIndex = 1;
            }
            $response = Http::withHeaders([
                'cookie' => $users[$userIndex],
                'x-ig-app-id' => '936619743392459',
                'Content-Type' => 'application/json',
            ])->get('https://www.instagram.com/graphql/query/?query_hash=e769aa130647d2354c40ea6a439bfc08&variables=' . $variables);

            for ($i = 0; $i < $count; $i++) {
                if ($i == count($response->json()['data']['user']['edge_owner_to_timeline_media']['edges'])) {
                    break;
                }
                $totalCommentCount += $response->json()['data']['user']['edge_owner_to_timeline_media']['edges'][$i]['node']['edge_media_to_comment']['count'];
                $totalLikeCount += $response->json()['data']['user']['edge_owner_to_timeline_media']['edges'][$i]['node']['edge_media_preview_like']['count'];
                $userName = $response->json()['data']['user']['edge_owner_to_timeline_media']['edges'][$i]['node']['owner']['username'];
                $index++;

            }

            $maxId = $response->json()['data']['user']['edge_owner_to_timeline_media']['page_info']['end_cursor'];
            $isMoreAvailable = $response->json()['data']['user']['edge_owner_to_timeline_media']['page_info']['has_next_page'];
            if ($isMoreAvailable) {
                $index = 0;
            }

        }
        $mediaCount = $response->json()['data']['user']['edge_owner_to_timeline_media']['count'];

        // echo "followersCount: " . $followersCount . '<br>';
        // echo "totalLikeCount: " . $totalLikeCount . '<br>';
        // echo "totalCommentCount: " . $totalCommentCount . '<br>';
        // echo "totalLikeCount + totalCommentCount: " . ($totalLikeCount + $totalCommentCount) . '<br>';
        // echo "userName: " . $userName . '<br>';
        // echo "userId:" . $userId . '<br>';
        // echo "mediaCount:" . $mediaCount . '<br>';
        // echo "biography: " . $biography . '<br>';
        // echo "engagementRate: " . number_format($percent, 2, ',', '.') . '%';

        $percent = ($totalLikeCount + $totalCommentCount) / $followersCount * 100;

        $sameAccount = ApiAccount::where("userId", $userId);
        $sameAccountCount = $sameAccount->count();
        if ($sameAccountCount == 1) {
            $sameAccount->update([
                "userName" => $userName,
                "engagementRate" => $percent,
            ]);
            if ($sameAccount) {
                echo "Update Successful";
            } else {
                echo "Update Failed";
            }
        } else {
            $account = new ApiAccount();
            $account->userId = $userId;
            $account->userName = $userName;
            $account->engagementRate = $percent;
            if ($account->save()) {
                echo "Registration Successful";
            } else {
                echo "Registration Failed";
            }
        }

    }
}
