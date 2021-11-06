<?php

namespace App\Http\Controllers;

use App\Services\NoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Note;
use App\Http\Requests\NoteStoreRequest;
use App\Http\Requests\NoteAddFileRequest;
use App\Http\Resources\NoteResource;

class NoteController extends Controller
{
    /**
     * @var NoteService
     */
    private $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->middleware('auth:api');
        $this->noteService = $noteService;
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

        return response ()->json (
            NoteResource::collection(
                $this->noteService->listNotes($page)
            )
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

        return response()->json(NoteResource::make($note));
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

        return response ()->json (NoteResource::make($note));
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
        return response ()->json (NoteResource::make($note));
    }

    /**
     * Adds a file to a note
     *
     * @param integer $id Note ID
     * @param NoteAddFileRequest $request
     *
     * @return JsonResponse
     */
    public function addfile($id, NoteAddFileRequest $request)
    {
        $note = Note::where ('id', $id)
            ->first ();

        if (! $note) {
            return response ()->json (['error' => 'not found'], 404);
        }

        if ($note->user_id != auth()->user()->id) {
            return response ()->json (['error' => 'not access'], 401);
        }

        $note = $this->noteService->addfile($note, $request->file ('attache'), $id);

        return response ()->json (NoteResource::make($note));
    }
}
