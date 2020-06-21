<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Note;

class NoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make ($request->all (), [
            'note' => 'required',
        ]);

        if ($validator->fails ()) {
            return response ()->json ($validator->getMessageBag (), 400);
        }

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
     * @return \Illuminate\Http\Response
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\JsonResponse
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
