<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Http;

class ContactController extends Controller
{
    private function authenticate(Request $request)
    {
        $token = $request->input('token') ?? $request->query('token');
        if (!$token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return null;
        }

        $user = $accessToken->tokenable;

        Auth::setUser($user);

        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->authenticate($request);

        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        return Contact::where('user_id', $user->id)->orderBy('name')->get();
    }

    public function search(Request $request)
    {
        $user = $this->authenticate($request);

        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $query = $request->input('query');
        if ($query === '--todos') {
            return Contact::where('user_id', $user->id)->orderBy('name')->get();
        }

        return Contact::where('user_id', $user->id)
                      ->where('name', 'like', "%{$query}%")
                      ->orderBy('name')
                      ->get();
    }

    private function geocode($address)
    {
        $url = 'https://nominatim.openstreetmap.org/search';
        $params = [
            'q' => $address,
            'format' => 'json',
            'limit' => 1
        ];

        // Adiciona um cabeçalho User-Agent à requisição
        $response = Http::withHeaders([
            'User-Agent' => 'MyApp/1.0'
        ])->get($url, $params);

        Log::info('Geocode URL', ['url' => $url, 'params' => $params]);
        Log::info('Geocode response', ['response' => $response->json()]);

        if ($response->successful() && count($response->json()) > 0) {
            return $response->json()[0];
        }

        Log::error('Geocode failed', ['response' => $response->body()]);
        return null;
    }

    public function store(Request $request)
    {
        $user = $this->authenticate($request);

        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:contacts',
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'cep' => 'required|string|size:8',
            'city' => 'required|string|max:255',
            'state' => 'required|string|size:2',
        ]);

        $addressWithCep = "{$validated['address']}, {$validated['city']}, {$validated['state']}, {$validated['cep']}";
        $addressWithoutCep = "{$validated['address']}, {$validated['city']}, {$validated['state']}";

        $geocode = $this->geocode($addressWithCep);

        if (!$geocode) {
            Log::info('Falha no geocódigo com CEP. Tentando novamente sem o cep...');
            $geocode = $this->geocode($addressWithoutCep);
        }

        if ($geocode) {
            $validated['latitude'] = $geocode['lat'];
            $validated['longitude'] = $geocode['lon'];
        } else {
            return response()->json(['error' => 'Não foi possível obter as coordenadas do endereço'], 400);
        }

        $validated['user_id'] = $user->id;

        return Contact::create($validated);
    }
}
