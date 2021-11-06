<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Note;
use App\Http\Requests\NoteStoreRequest;

class NoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $page = (int)$request->input ('page');
        if ($page < 1) {
            $page = 1;
        }

        $limit  = 10;
        $offset = ( $page - 1 ) * $limit;

        return response ()->json (
            Note::where ('user_id', \JWTAuth::parseToken()->authenticate()->id)
                ->skip ($offset)->take ($limit)
                ->get ()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param NoteStoreRequest $request
     * @return JsonResponse
     */
    public function store(NoteStoreRequest $request)
    {
        $note = Note::create([
            'text' => $request->note,
            'user_id' => auth()->user()->id,
        ]);

        return response()->json($note);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $note = Note::where ('id', $id)
            ->first ();

        if (! $note) {
            return response ()->json (['error' => 'not found'], 404);
        }

        if ($note->user_id != auth()->user()->id) {
            return response ()->json (['error' => 'not access'], 401);
        }

        return response ()->json ($note);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $note = Note::where ('id', $id)
            ->first ();

        if(! $note) {
            return response ()->json (['error' => 'not found'], 404);
        }

        if ($note->user_id != auth()->user()->id) {
            return response ()->json (['error' => 'not access'], 401);
        }

        $note->delete();
        return response ()->json (['deleted' => $id]);
    }

    /**
     * Recovers deleted note
     *
     * @param integer $id Note ID
     *
     * @return JsonResponse
     */
    public function restore($id)
    {
        $note = Note::withTrashed()
            ->where ('id', $id)
            ->first ();

        if (! $note) {
            return response ()->json (['error' => 'not found'], 404);
        }

        if ($note->user_id != auth()->user()->id) {
            return response ()->json (['error' => 'not access'], 401);
        }

        $note->restore();
        return response ()->json ($note);
    }

    /**
     * Adds a file to a note
     *
     * @param integer $id      Note ID
     * @param Request $request Request
     *
     * @return JsonResponse
     */
    public function addfile($id, Request $request)
    {
        $note = Note::where ('id', $id)
            ->first ();

        if (! $note) {
            return response ()->json (['error' => 'not found'], 404);
        }

        if ($note->user_id != auth()->user()->id) {
            return response ()->json (['error' => 'not access'], 401);
        }

        $validator = Validator::make ($request->all (), [
            'attache' => 'required|mimes:jpeg,png',
        ]);

        if ($validator->fails ()) {
            return response ()->json ($validator->messages (), 400);
        }

        $attache  = $request->file ('attache');
        $fileName = $id . '.' . $attache->getClientOriginalExtension ();

        if ($note->file && Storage::exists($note->file)) {
            Storage::delete($note->file);
        }

        Storage::put($fileName, file_get_contents($attache));
        unlink ($attache->getPathname ());

        $note->file = $fileName;
        $note->save ();

        return response ()->json ($note);
    }
}
