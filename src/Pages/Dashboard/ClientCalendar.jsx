import React, { useState, useEffect } from 'react';
import './ClientCalendar.css';

const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

const ClientCalendar = () => {
  const [events, setEvents] = useState([]);
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [date] = useState(new Date());
  const [month, setMonth] = useState(date.getMonth());
  const [year, setYear] = useState(date.getFullYear());

  const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];

  const shortDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      // Fetch approved events
      const eventsResponse = await fetch(`${API_BASE_URL}/get_approved_requests.php`);
      const eventsData = await eventsResponse.json();
      
      // Fetch pending requests
      const requestsResponse = await fetch(`${API_BASE_URL}/get_requests.php`);
      const requestsData = await requestsResponse.json();
      
      if (eventsData.success) {
        setEvents(eventsData.events || []);
      }
      
      if (requestsData.success) {
        setRequests(requestsData.requests || []);
      }
      
      setLoading(false);
    } catch (error) {
      console.error('Error fetching data:', error);
      setLoading(false);
    }
  };

  const getEventDate = (event) => {
    try {
      const dateStr = event.date_need_from || event.date || '';
      return dateStr ? new Date(dateStr) : null;
    } catch (e) {
      return null;
    }
  };

  const renderCalendar = () => {
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    let dates = [];
    const totalCells = 7 * 6;

    // Previous month days
    for (let i = 0; i < firstDay; i++) {
      dates.push({
        day: daysInPrevMonth - firstDay + i + 1,
        currentMonth: false,
        isToday: false
      });
    }

    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
      const isToday =
        i === date.getDate() &&
        month === new Date().getMonth() &&
        year === new Date().getFullYear();

      // Check for approved events (green)
      const hasApprovedEvent = events.some(event => {
        const eventDate = getEventDate(event);
        return eventDate &&
          eventDate.getDate() === i &&
          eventDate.getMonth() === month &&
          eventDate.getFullYear() === year;
      });

      // Check for pending requests (blue)
      const hasPendingRequest = requests.some(request => {
        const requestDate = getEventDate(request);
        return requestDate &&
          requestDate.getDate() === i &&
          requestDate.getMonth() === month &&
          requestDate.getFullYear() === year;
      });

      dates.push({
        day: i,
        currentMonth: true,
        isToday: isToday,
        hasApprovedEvent: hasApprovedEvent,
        hasPendingRequest: hasPendingRequest
      });
    }

    // Next month days
    const remainingCells = totalCells - dates.length;
    for (let i = 1; i <= remainingCells; i++) {
      dates.push({
        day: i,
        currentMonth: false,
        isToday: false
      });
    }

    let rows = [];
    for (let i = 0; i < totalCells; i += 7) {
      rows.push(dates.slice(i, i + 7));
    }

    return rows.map((week, weekIndex) => (
      <tr key={`week-${weekIndex}`}>
        {week.map((dayObj, dayIndex) => (
          <td
            key={`day-${weekIndex}-${dayIndex}`}
            className={`
              ${!dayObj.currentMonth ? 'inactive' : ''} 
              ${dayObj.isToday ? 'today' : ''} 
              ${dayObj.hasApprovedEvent ? 'has-approved-event' : ''} 
              ${dayObj.hasPendingRequest ? 'has-pending-request' : ''}
            `}
          >
            {dayObj.day}
          </td>
        ))}
      </tr>
    ));
  };

  const handleNavClick = (direction) => {
    let newMonth = month;
    let newYear = year;

    if (direction === "prev") {
      if (month === 0) {
        newYear--;
        newMonth = 11;
      } else {
        newMonth--;
      }
    } else {
      if (month === 11) {
        newYear++;
        newMonth = 0;
      } else {
        newMonth++;
      }
    }

    setMonth(newMonth);
    setYear(newYear);
  };

  const upcomingEvents = events.filter(event => {
    const eventDate = getEventDate(event);
    return eventDate && eventDate >= new Date();
  }).slice(0, 5);

  return (
    <div className="client-calendar">
      <h2 className="calendar-title">Event Calendar</h2>
      
      <div className="calendar-nav">
        <button onClick={() => handleNavClick("prev")} className="nav-button">
          <i className='bx bx-chevron-left'></i>
        </button>
        <h3>{months[month]} {year}</h3>
        <button onClick={() => handleNavClick("next")} className="nav-button">
          <i className='bx bx-chevron-right'></i>
        </button>
      </div>

      <div className="calendar-legend">
        <div className="legend-item">
          <div className="legend-color approved"></div>
          <span>Approved Events</span>
        </div>
        <div className="legend-item">
          <div className="legend-color pending"></div>
          <span>Pending Requests</span>
        </div>
      </div>

      <table className="calendar-table">
        <thead>
          <tr>
            {shortDays.map(day => (
              <th key={day}>{day}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {loading ? (
            <tr>
              <td colSpan="7" className="loading-cell">Loading calendar...</td>
            </tr>
          ) : (
            renderCalendar()
          )}
        </tbody>
      </table>

      <div className="upcoming-events">
        <h4>Upcoming Events</h4>
        {upcomingEvents.length > 0 ? (
          <ul className="event-list">
            {upcomingEvents.map(event => (
              <li key={event.id} className="event-item">
                <div className="event-title">{event.activity}</div>
                <div className="event-date">
                  {getEventDate(event)?.toLocaleDateString()}
                </div>
                <div className="event-venue">{event.venue}</div>
              </li>
            ))}
          </ul>
        ) : (
          <p>No upcoming events</p>
        )}
      </div>
    </div>
  );
};

export default ClientCalendar;