<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User as User;
use App\Library as Library;

/**
 * @todo  Leverage Polymorphism, morphTo for `$item_type`, `$this->typeToFqc()`.
 */
class LibraryController extends Controller
{

    /**
     * For the current user; adds an item to the Library,
     * if its already exists, it is removed.
     *
     * @param  Request $request The HTTP request
     * @param  Library $library The Library model
     * @return Object           JsonResponse
     */
    // @todo update unit test for new return
    public function toggle(Request $request, Library $library): \Illuminate\Http\JsonResponse
    {
        $fqc = $this->typeToFqc($request->item_type);
        $foundItem = Library::where([
            'item_id'   => $request->item_id,
            'item_type' => $fqc,
        ])->with($request->item_type)->get()->first();

        if (is_null($foundItem)) {
            if ($request->item_type === 'event') {
                $foundItem = $fqc::with('venue.city.states')->find($request->item_id);
            } else {
                $foundItem = $fqc::find($request->item_id);
            }

            $library->item_id   = $foundItem->id;
            $library->item_type = $fqc;
            $library->user_id   = Auth::id();
            $library->save();
            $attached = $foundItem;
        } else {
            $library->destroy($foundItem->id);
            $detached = $foundItem['event'];
        }

        return response()->json([
            'attached' => $attached ?? false,
            'detached' => $detached ?? false,
        ]);
    }

    /**
     * Retrieve all IDs for items in current users Library.
     *
     * @return Object   JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $libraries = Auth::user()
            ->library()
            ->with(['event.venue.city.states', 'venue', 'schedule'])
            ->get()
            ->groupBy('item_type')
            ->toArray();

        $filtered = [];

        // Warning, keep the below code collapsed!
        // Nothing to see here, move on, keep moving...
        foreach ($libraries as $item => $values) {
            if ($item == 'App\Event') {
                foreach ($values as $value) {
                    $filtered[$item][] = $value['event'];
                }
            } elseif ($item == 'App\Schedule') {
                foreach ($values as $value) {
                    $filtered[$item][] = $value['schedule'];
                }
            } elseif ($item == 'App\Venue') {
                foreach ($values as $value) {
                    $filtered[$item][] = $value['venue'];
                }
            }
        }

        return response()->json($filtered);
    }

    /**
     * Converts the "item type", i.e, event, venue, schedule to the
     * fully qualified class name, i.e., App\Event.
     *
     * @todo  This can/should be handled via polymorphism, but this works
     * for now.
     * @param  string $type The item type.
     * @return string
     */
    // @todo throw 404 exception
    public function typeToFqc($type = null): string
    {
        switch ($type) {
            case 'event':
                $modelFqc = 'App\Event';
                break;
            case 'venue';
                $modelFqc = 'App\Venue';
                break;
            case 'schedule';
                $modelFqc = 'App\Schedule';
                break;
            default:
                $modelFqc = '';
                break;
        }
        return $modelFqc;
    }
}
