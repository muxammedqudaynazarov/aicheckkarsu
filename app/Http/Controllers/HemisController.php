<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;

class HemisController extends Controller
{
    private function getProvider()
    {
        return new GenericProvider([
            'clientId' => env('HEMIS_CLIENT_ID'),
            'clientSecret' => env('HEMIS_CLIENT_SECRET'),
            'redirectUri' => env('HEMIS_REDIRECT_URI'),
            'urlAuthorize' => 'https://hemis.karsu.uz/oauth/authorize',
            'urlAccessToken' => 'https://hemis.karsu.uz/oauth/access-token',
            'urlResourceOwnerDetails' => 'https://hemis.karsu.uz/oauth/api/user?fields=id,uuid,employee_id_number,name,firstname,surname,patronymic,birth_date,university_id,phone'
        ]);
    }

    public function user(Request $request)
    {
        $provider = $this->getProvider();
        if (!$request->has('code')) {
            $authorizationUrl = $provider->getAuthorizationUrl();
            $request->session()->put('oauth2state', $provider->getState());
            return redirect()->away($authorizationUrl);
        }
        $state = $request->input('state');
        $sessionState = $request->session()->pull('oauth2state');
        if (empty($state) || $state !== $sessionState) {
            return response('Xatolik: Yaroqsiz state', 400);
        }
        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);
            $resourceOwner = $provider->getResourceOwner($accessToken);
            $user_data = $resourceOwner->toArray();
            $dep_id = null;
            foreach ($user_data['departments'] as $department) {
                if ($department['employmentForm']['code'] == '11') $dep_id = $department['department']['id'];
            }
            $department = Department::find($dep_id);
            if ($department)
                $dep_id = $department->parent_id ?: $department->id;
            else $dep_id = null;
            //dd($user_data);
            $existingUser = User::find($user_data['employee_id']);
            $roleToAssign = $existingUser ? $existingUser->pos : 'user';
            $user = User::updateOrCreate([
                'id' => $user_data['employee_id'],
            ], [
                'name' => json_encode([
                    'full_name' => $user_data['name'],
                    'short_name' => $user_data['surname'] . ' ' . $user_data['firstname'][0] . '.',
                    'surname' => $user_data['surname'],
                    'firstname' => $user_data['firstname'],
                    'patronymic' => $user_data['patronymic'],
                ]),
                'image' => $user_data['picture'],
                'employee_id_number' => $user_data['employee_id_number'],
                'department_id' => $dep_id,
                'phone' => $user_data['phone'],
                'pos' => $roleToAssign,
            ]);
            $user->syncRoles($roleToAssign);
            Auth::login($user);
            return redirect()->route('home')->with('success', 'Tizimga kirdingiz.');
        } catch (IdentityProviderException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
