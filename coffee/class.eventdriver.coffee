###	
EventDriver - Richard Lyon, Feb 24 2011

A simple class to allow jQuery-like trigger+bind events

For example:

	a = new EventDriver; (or an object that extends it, as intended)
	b = (function(x,y) { console.log(this,x,y) } )

	a.bind( 'yoyo', b )
	a.trigger( 'yoyo', 3.1412, 42 )

Results in "[Object EventDriver], 3.1412, 42" being logged (or something similar)
###

class EventDriver
	events: {}

	bind: ( event, fn ) ->
		if not @bound event
			@events[event] = []
		@events[event].push fn

	trigger: (event, args...) ->
		if @bound event
			f.apply this, args for f in @events[event]

	bound: (event) ->
		return @events[event]?