/**
 * External dependencies
 */
import { Calendar as FullCalendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';

import '@fullcalendar/core/main.css';
import '@fullcalendar/daygrid/main.css';
import '@fullcalendar/timegrid/main.css';
import '@fullcalendar/list/main.css';

/**
 * Internal dependencies
 */
import './styles.css';

const getMeetings = ( calendarEl ) => {
	return JSON.parse( calendarEl.getAttribute( 'data-meetings' ) );
};

const formatMeeting = ( i ) => {
	return {
		...i,
		title: i.title.rendered,
		start: i.date,
	};
};

const getCalendar = ( calendarEl, events ) => {
	return new FullCalendar( calendarEl, {
		plugins: [ dayGridPlugin, timeGridPlugin, listPlugin ],
		events,
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'dayGridMonth, timeGridWeek, timeGridDay',
		},
		height: 'auto'
	} );
};

const removeLoadingState = ( calendarEl ) => {
	calendarEl.innerHTML = '';
};

const initCalendar = () => {
	const calendarEl = document.getElementById( 'wporg-meeting-calendar-js' );

	const meetingData = getMeetings( calendarEl );
	const events = meetingData.map( formatMeeting );
	const calendar = getCalendar( calendarEl, events );

	removeLoadingState( calendarEl );
	calendar.render();
};

document.addEventListener( 'DOMContentLoaded', initCalendar );