<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Carbon;

class EventController extends Controller
{
    public function index()
    {
        $search = request('search');

        if ($search) {
            $events = Event::where([
                ['title', 'like', '%'.$search.'%']
            ])->get();
        } 
        else 
        {
            $events = Event::all();
        }
        
        return view('events.index', [
            'events' => $events,
            'search' => $search
        ]);
    }

    public function show(int $id)
    {
        $event = Event::findOrFail($id);

        $user = auth()->user();

        $hasUserJoined = false;

        if($user)
        {
            $userEvents = $user->eventsAsParticipant->toArray();

            foreach($userEvents as $userEvent)
            {
                if ($userEvent['id'] == $id) {
                    $hasUserJoined = true;
                }
            }
        }

        return view('events.show', [
            'event' => $event,
            'hasUserJoined' => $hasUserJoined
        ]);
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $event = new Event;

        $event->title = $request->title;
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;
        $event->items = $request->items;
        $event->date = $request->date;

        if($request->hasFile('image') && $request->file('image')->isValid())
        {
            $requestImage = $request->image;
            $extension = $requestImage->extension();
            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now") . "." . $extension);
            $request->image->move(public_path('img/events'), $imageName);
            $event->image = $imageName;
        }

        $user = auth()->user();
        $event->user_id = $user->id;

        $event->save();

        return to_route('events.index')->with('msg', 'Evento criado com sucesso!');
    }

    public function edit(int $id)
    {
        $user = auth()->user();

        $event = Event::findOrFail($id);

        if ($user->id != $event->user_id) {
            return to_route('events.dashboard');
        }

        return view('events.edit', [
            'event' => $event
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->all();

        if($request->hasFile('image') && $request->file('image')->isValid())
        {
            $requestImage = $request->image;
            $extension = $requestImage->extension();
            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now") . "." . $extension);
            $request->image->move(public_path('img/events'), $imageName);
            $data['image'] = $imageName;
        }
        
        Event::findOrFail($request->id)->update($data);

        return to_route('events.index')->with('msg', 'Evento atualizado com sucesso!');
    }

    public function destroy(int $id)
    {
        Event::findOrFail($id)->delete();

        return to_route('events.dashboard')->with('msg', 'Evento excluído com sucesso!');
    }

    public function dashboard()
    {
        $user = auth()->user();
        $events = $user->events;
        $eventsAsParticipant = $user->eventsAsParticipant;

        return view('events.dashboard', [
            'events' => $events,
            'eventsAsParticipant' => $eventsAsParticipant
        ]);
    }

    public function joinEvent(int $id)
    {
        $user = auth()->user();

        $user->eventsAsParticipant()->attach($id);

        $event = Event::findOrFail($id);

        return to_route('events.dashboard')->with('msg', 'Sua presença foi confimada no evento: ' . $event->title);
    }

    public function leaveEvent(int $id)
    {
        $user = auth()->user();

        $user->eventsAsParticipant()->detach($id);

        $event = Event::findOrFail($id);

        return to_route('events.dashboard')->with('msg', 'Você saiu com sucesso do evento: ' . $event->title);
    }
}
