<?php

namespace App\Http\Controllers;

use App\Models\Surah;
use App\Models\Ayah;
use Illuminate\Http\Request;

class ReadController extends Controller
{
    /**
     * Display the public Surah reading page.
     */
    public function show(Surah $surah)
    {
        // Fetch active ayahs with their translations
        $ayahs = $surah->ayahs()
            ->active()
            ->with(['translations' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('ayah_number')
            ->get();

        $surahs = Surah::active()->orderBy('number')->get();

        return view('read.show', [
            'mode' => 'surah',
            'title' => $surah->name_en . ' - ' . $surah->name_ar,
            'subtitle' => $surah->name_ku ?? '',
            'surah' => $surah,
            'ayahs' => $ayahs,
            'surahs' => $surahs,
        ]);
    }

    /**
     * Display the public Juz reading page.
     */
    public function juz($juz_number)
    {
        $juz = (int) $juz_number;
        if ($juz < 1 || $juz > 30) {
            abort(404);
        }

        // Fetch active ayahs in the Juz with their parent Surah and translations
        $ayahs = Ayah::active()
            ->where('juz_number', $juz)
            ->with(['surah', 'translations' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        $surahs = Surah::active()->orderBy('number')->get();

        return view('read.show', [
            'mode' => 'juz',
            'title' => 'Juz ' . $juz,
            'subtitle' => 'جوزئی ' . $juz,
            'juz_number' => $juz,
            'ayahs' => $ayahs,
            'surahs' => $surahs,
        ]);
    }

    /**
     * Display the list of all Juzs.
     */
    public function juzIndex()
    {
        $juzs = \Illuminate\Support\Facades\Cache::remember('juz_list_' . app()->getLocale(), 3600, function () {
            $juzs = [];
            for ($i = 1; $i <= 30; $i++) {
                $firstAyah = Ayah::where('juz_number', $i)->with('surah')->orderBy('surah_id')->orderBy('ayah_number')->first();
                $lastAyah = Ayah::where('juz_number', $i)->with('surah')->orderBy('surah_id', 'desc')->orderBy('ayah_number', 'desc')->first();
                
                $juzs[] = [
                    'number' => $i,
                    'start_surah_name' => $firstAyah ? ($firstAyah->surah->{'name_' . app()->getLocale()} ?? $firstAyah->surah->name_en) : '',
                    'start_surah_name_ar' => $firstAyah ? $firstAyah->surah->name_ar : '',
                    'start_ayah' => $firstAyah ? $firstAyah->ayah_number : '',
                    'end_surah_name' => $lastAyah ? ($lastAyah->surah->{'name_' . app()->getLocale()} ?? $lastAyah->surah->name_en) : '',
                    'end_surah_name_ar' => $lastAyah ? $lastAyah->surah->name_ar : '',
                    'end_ayah' => $lastAyah ? $lastAyah->ayah_number : '',
                ];
            }
            return $juzs;
        });

        return view('juz.index', compact('juzs'));
    }

    /**
     * Display the list of all Pages.
     */
    public function pageIndex(Request $request)
    {
        $pages = \Illuminate\Support\Facades\Cache::remember('page_list_' . app()->getLocale(), 3600, function () {
            $pages = [];
            for ($i = 1; $i <= 604; $i++) {
                $firstAyah = Ayah::where('page_number', $i)->with('surah')->orderBy('surah_id')->orderBy('ayah_number')->first();
                $lastAyah = Ayah::where('page_number', $i)->with('surah')->orderBy('surah_id', 'desc')->orderBy('ayah_number', 'desc')->first();
                
                if ($firstAyah && $lastAyah) {
                    $startSurah = $firstAyah->surah->{'name_' . app()->getLocale()} ?? $firstAyah->surah->name_en;
                    $endSurah = $lastAyah->surah->{'name_' . app()->getLocale()} ?? $lastAyah->surah->name_en;
                    
                    if ($firstAyah->surah_id === $lastAyah->surah_id) {
                        $range = $startSurah . ' (' . $firstAyah->ayah_number . '-' . $lastAyah->ayah_number . ')';
                    } else {
                        $range = $startSurah . ' (' . $firstAyah->ayah_number . ') - ' . $endSurah . ' (' . $lastAyah->ayah_number . ')';
                    }
                } else {
                    $range = '';
                }
                
                $pages[] = [
                    'number' => $i,
                    'range' => $range,
                ];
            }
            return $pages;
        });

        $pageNumber = (int) $request->query('page', 1);
        if ($pageNumber < 1) $pageNumber = 1;
        $perPage = 48;
        
        $offset = ($pageNumber - 1) * $perPage;
        $slicedPages = array_slice($pages, $offset, $perPage);
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $slicedPages,
            count($pages),
            $perPage,
            $pageNumber,
            ['path' => route('page.index'), 'query' => $request->query()]
        );

        return view('pages.index', ['pages' => $paginator]);
    }

    /**
     * Display the public Page reading page.
     */
    public function page($page_number)
    {
        $page = (int) $page_number;
        if ($page < 1 || $page > 604) {
            abort(404);
        }

        $ayahs = Ayah::active()
            ->where('page_number', $page)
            ->with(['surah', 'translations' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        $surahs = Surah::active()->orderBy('number')->get();

        return view('read.show', [
            'mode' => 'page',
            'title' => 'Page ' . $page,
            'subtitle' => 'لاپەڕەی ' . $page,
            'page_number' => $page,
            'ayahs' => $ayahs,
            'surahs' => $surahs,
        ]);
    }
}
