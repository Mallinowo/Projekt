<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'name' => 'Klaudia',
                'email' => 'moon@demo.pl',
                'age' => 22,
                'city' => 'Krakow',
                'city_lat' => 50.06143,
                'city_lng' => 19.93658,
                'sub' => 'emo',
                'gender' => 'female',
                'orientation' => 'bi',
                'bio' => 'Zyje w rytmie MCR i Three Days Grace. Nocny wedrowiec, rysownik.',
                'interests' => ['punk rock', 'digital art', 'dark fiction', 'astronomy'],
                'artists' => ['My Chemical Romance', 'Three Days Grace', 'Bring Me The Horizon'],
                'photo' => ['file' => 'klaudia.png', 'initials' => 'K', 'colors' => ['#24103f', '#102034', '#c026d3']],
            ],
            [
                'name' => 'Krystian',
                'email' => 'void@demo.pl',
                'age' => 24,
                'city' => 'Wroclaw',
                'city_lat' => 51.10788,
                'city_lng' => 17.03854,
                'sub' => 'goth',
                'gender' => 'male',
                'orientation' => 'hetero',
                'bio' => 'Goth od urodzenia. Uwielbiam kawe, cmentarne spacery i Death Note.',
                'interests' => ['gothic literature', 'photography', 'coffee'],
                'artists' => ['Bauhaus', 'The Cure', 'Sisters of Mercy'],
                'photo' => ['file' => 'krystian.png', 'initials' => 'K', 'colors' => ['#120d20', '#24233a', '#6d28d9']],
            ],
            [
                'name' => 'Julka',
                'email' => 'goth@demo.pl',
                'age' => 19,
                'city' => 'Gdansk',
                'city_lat' => 54.35205,
                'city_lng' => 18.64637,
                'sub' => 'scene',
                'gender' => 'female',
                'orientation' => 'homo',
                'bio' => 'Scene queen in 2024. Koloruje wlosy co miesiac.',
                'interests' => ['hair styling', 'concerts', 'gaming'],
                'artists' => ['Falling In Reverse', 'Palaye Royale', 'Motionless in White'],
                'photo' => ['file' => 'julka.png', 'initials' => 'J', 'colors' => ['#1f0b2e', '#0e2336', '#ec4899']],
            ],
            [
                'name' => 'Oliwier',
                'email' => 'rain@demo.pl',
                'age' => 26,
                'city' => 'Poznan',
                'city_lat' => 52.40637,
                'city_lng' => 16.92517,
                'sub' => 'punk',
                'gender' => 'male',
                'orientation' => 'bi',
                'bio' => 'DIY or die. Gram w kapeli od 4 lat.',
                'interests' => ['DIY punk', 'skating', 'zines'],
                'artists' => ['The Misfits', 'NOFX', 'Bad Brains'],
                'photo' => ['file' => 'oliwier.png', 'initials' => 'O', 'colors' => ['#1b1026', '#17251f', '#22c55e']],
            ],
            [
                'name' => 'Luciusz',
                'email' => 'crypt@demo.pl',
                'age' => 21,
                'city' => 'Lodz',
                'city_lat' => 51.75925,
                'city_lng' => 19.45598,
                'sub' => 'metalhead',
                'gender' => 'nonbinary',
                'orientation' => 'other',
                'bio' => 'Brutal ale wrazliwy. Opeth to moj ulubiony zespol.',
                'interests' => ['progressive metal', 'coding', 'hiking'],
                'artists' => ['Opeth', 'Tool', 'Mastodon'],
                'photo' => ['file' => 'luciusz.png', 'initials' => 'L', 'colors' => ['#111827', '#2b1638', '#f97316']],
            ],
        ];

        foreach ($profiles as $profile) {
            $photoPath = $this->createDemoPhoto($profile);

            $user = User::updateOrCreate([
                'email' => $profile['email'],
            ], [
                'name' => $profile['name'],
                'password' => Hash::make('demo123'),
                'age' => $profile['age'],
                'city' => $profile['city'],
                'city_lat' => $profile['city_lat'],
                'city_lng' => $profile['city_lng'],
                'preferred_age_min' => 18,
                'preferred_age_max' => 99,
                'preferred_max_distance_km' => 120,
                'preferred_subcultures' => User::SUBCULTURE_VALUES,
                'gender' => $profile['gender'],
                'orientation' => $profile['orientation'],
                'subculture' => $profile['sub'],
                'bio' => $profile['bio'],
                'avatar' => $photoPath,
                'onboarding_completed' => true,
            ]);

            $user->photos()->delete();
            $user->interests()->delete();
            $user->artists()->delete();

            foreach ($profile['interests'] as $interest) {
                $user->interests()->create(['name' => $interest]);
            }

            foreach ($profile['artists'] as $artist) {
                $user->artists()->create(['name' => $artist]);
            }

            $user->photos()->create([
                'path' => $photoPath,
                'order' => 0,
            ]);
        }

        Cache::add('discover_cache_version', 1);
        Cache::increment('discover_cache_version');
        Cache::add('api_cache_version', 1);
        Cache::increment('api_cache_version');
    }

    private function createDemoPhoto(array $profile): string
    {
        $path = 'demo-profiles/' . $profile['photo']['file'];
        $colors = $profile['photo']['colors'];

        $width = 900;
        $height = 1200;
        $image = imagecreatetruecolor($width, $height);

        [$r1, $g1, $b1] = $this->hexToRgb($colors[0]);
        [$r2, $g2, $b2] = $this->hexToRgb($colors[1]);

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $r = (int) round($r1 + ($r2 - $r1) * $ratio);
            $g = (int) round($g1 + ($g2 - $g1) * $ratio);
            $b = (int) round($b1 + ($b2 - $b1) * $ratio);
            imageline($image, 0, $y, $width, $y, imagecolorallocate($image, $r, $g, $b));
        }

        [$ar, $ag, $ab] = $this->hexToRgb($colors[2]);
        $accent = imagecolorallocatealpha($image, $ar, $ag, $ab, 42);
        $soft = imagecolorallocatealpha($image, 255, 255, 255, 108);
        $dark = imagecolorallocatealpha($image, 0, 0, 0, 95);

        imagefilledellipse($image, 190, 210, 420, 420, $accent);
        imagefilledellipse($image, 760, 930, 560, 560, $accent);
        imagefilledellipse($image, 450, 560, 520, 520, $dark);
        imagefilledellipse($image, 450, 520, 330, 330, $soft);

        $text = strtoupper((string) $profile['photo']['initials']);
        $textColor = imagecolorallocate($image, 245, 240, 255);
        $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 70);
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = (int) (($width - $textWidth) / 2);
        $y = (int) (($height - $textHeight) / 2);

        imagestring($image, $font, $x + 3, $y + 3, $text, $shadowColor);
        imagestring($image, $font, $x, $y, $text, $textColor);

        ob_start();
        imagepng($image, null, 9);
        $png = ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($path, $png);

        return $path;
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
