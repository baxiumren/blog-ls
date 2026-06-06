<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class ApiFootball
{
    // "Pintu" ke API: udah otomatis pasang key + base URL
    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'x-apisports-key' => config('services.api_football.key'),
        ])->baseUrl(config('services.api_football.url'));
    }

    // Ambil detail 1 liga (buat ngisi tabel leagues + tau season aktif)
    public function league(int $leagueId): ?array
    {
        return $this->client()
            ->get('/leagues', ['id' => $leagueId])
            ->json('response.0');
    }

    // Ambil semua tim di 1 liga + season
    public function teams(int $leagueId, int $season): array
    {
        return $this->client()
            ->get('/teams', ['league' => $leagueId, 'season' => $season])
            ->json('response') ?? [];
    }

    // Ambil semua fixtures di 1 liga + season
    public function fixtures(int $leagueId, int $season): array
    {
        return $this->client()
            ->get('/fixtures', ['league' => $leagueId, 'season' => $season])
            ->json('response') ?? [];
    }

    // Semua match yang lagi berlangsung (buat live auto-refresh)
    public function live(): array
    {
        return $this->client()
            ->get('/fixtures', ['live' => 'all'])
            ->json('response') ?? [];
    }

    // Semua fixture di 1 tanggal (buat sync harian — 1 call, update skor/status/FT)
    public function fixturesByDate(string $date): array
    {
        return $this->client()
            ->get('/fixtures', ['date' => $date])
            ->json('response') ?? [];
    }
    
    // Events 1 match (gol, kartu, pergantian) — buat timeline Overview
    public function events(int $fixtureId): array
    {
        return $this->client()
            ->get('/fixtures/events', ['fixture' => $fixtureId])
            ->json('response') ?? [];
    }

    // Statistik 1 match (tembakan, penguasaan bola, dll) — per tim
    public function statistics(int $fixtureId): array
    {
        return $this->client()
            ->get('/fixtures/statistics', ['fixture' => $fixtureId])
            ->json('response') ?? [];
    }

    // Susunan pemain 1 match (formasi + starting XI)
    public function lineups(int $fixtureId): array
    {
        return $this->client()
            ->get('/fixtures/lineups', ['fixture' => $fixtureId])
            ->json('response') ?? [];
    }

    // Detail 1 fixture (venue, wasit, round, skor penalti)
    public function fixture(int $fixtureId): ?array
    {
        return $this->client()
            ->get('/fixtures', ['id' => $fixtureId])
            ->json('response.0');
    }

    // Riwayat pertemuan 2 tim
    public function headToHead(int $homeId, int $awayId, int $last = 10): array
    {
        return $this->client()
            ->get('/fixtures/headtohead', ['h2h' => $homeId . '-' . $awayId, 'last' => $last])
            ->json('response') ?? [];
    }

    // Statistik pemain per match (rating, foto, kapten)
    public function players(int $fixtureId): array
    {
        return $this->client()
            ->get('/fixtures/players', ['fixture' => $fixtureId])
            ->json('response') ?? [];
    }

    // Prediksi pertandingan (Who will win)
    public function predictions(int $fixtureId): ?array
    {
        return $this->client()
            ->get('/predictions', ['fixture' => $fixtureId])
            ->json('response.0');
    }

    // Detail 1 tim (termasuk venue/stadion)
    public function team(int $teamId): ?array
    {
        return $this->client()
            ->get('/teams', ['id' => $teamId])
            ->json('response.0');
    }
    
    // Statistik pemain 1 tim semusim (gabung semua halaman)
    public function squadStats(int $teamId, int $season): array
    {
        $all = [];
        $page = 1;
        do {
            $res = $this->client()->get('/players', ['team' => $teamId, 'season' => $season, 'page' => $page])->json();
            $all = array_merge($all, $res['response'] ?? []);
            $totalPages = $res['paging']['total'] ?? 1;
            $page++;
        } while ($page <= $totalPages && $page <= 5);

        return $all;
    }
    
    // Semua fixtures 1 tim di 1 musim (buat hitung win%)
    public function teamFixtures(int $teamId, int $season): array
    {
        return $this->client()
            ->get('/fixtures', ['team' => $teamId, 'season' => $season])
            ->json('response') ?? [];
    }
    
    // Daftar pelatih 1 tim (current + history)
    public function coachs(int $teamId): array
    {
        return $this->client()
            ->get('/coachs', ['team' => $teamId])
            ->json('response') ?? [];
    }
    
    // Daftar skuad 1 tim (nomor punggung + posisi grup)
    public function squad(int $teamId): array
    {
        return $this->client()
            ->get('/players/squads', ['team' => $teamId])
            ->json('response.0.players') ?? [];
    }
    
    // Statistik 1 tim di 1 liga semusim (W/D/L, gol, clean sheet, kartu, dll)
    public function teamStatistics(int $leagueId, int $teamId, int $season): array
    {
        return $this->client()
            ->get('/teams/statistics', ['league' => $leagueId, 'team' => $teamId, 'season' => $season])
            ->json('response') ?? [];
    }
    
    // Transfer pemain masuk/keluar 1 tim
    public function transfers(int $teamId): array
    {
        return $this->client()
            ->get('/transfers', ['team' => $teamId])
            ->json('response') ?? [];
    }
    
    // Profil + statistik 1 pemain semusim (per kompetisi)
    public function player(int $id, int $season): ?array
    {
        return $this->client()
            ->get('/players', ['id' => $id, 'season' => $season])
            ->json('response.0');
    }
    
    // Cari pemain by nama (live, buat search)
    public function searchPlayers(string $q): array
    {
        return $this->client()
            ->get('/players/profiles', ['search' => $q])
            ->json('response') ?? [];
    }
    
    // Trofi 1 pemain
    public function playerTrophies(int $id): array
    {
        return $this->client()->get('/trophies', ['player' => $id])->json('response') ?? [];
    }

    // Karir klub 1 pemain
    public function playerTeams(int $id): array
    {
        return $this->client()->get('/players/teams', ['player' => $id])->json('response') ?? [];
    }
    
    // Klasemen 1 liga (array grup; cup bisa banyak grup)
    public function standings(int $leagueId, int $season): array
    {
        return $this->client()->get('/standings', ['league' => $leagueId, 'season' => $season])->json('response.0.league.standings') ?? [];
    }

    // Top skorer liga
    public function topScorers(int $leagueId, int $season): array
    {
        return $this->client()->get('/players/topscorers', ['league' => $leagueId, 'season' => $season])->json('response') ?? [];
    }

    // Top assist liga
    public function topAssists(int $leagueId, int $season): array
    {
        return $this->client()->get('/players/topassists', ['league' => $leagueId, 'season' => $season])->json('response') ?? [];
    }
    
    // Top kartu kuning liga
    public function topYellowCards(int $leagueId, int $season): array
    {
        return $this->client()->get('/players/topyellowcards', ['league' => $leagueId, 'season' => $season])->json('response') ?? [];
    }

    // Status akun API (kuota terpakai hari ini + limit + plan)
    public function status(): ?array
    {
        return $this->client()->get('/status')->json('response');
    }
}