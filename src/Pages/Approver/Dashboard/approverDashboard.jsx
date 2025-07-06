import React, { useState, useEffect } from 'react';
import './approverDashboard.css';

const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

const ApproverDashboard = () => {
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [date] = useState(new Date());
  const [month, setMonth] = useState(date.getMonth());
  const [year, setYear] = useState(date.getFullYear());

  const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];

  const shortDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  useEffect(() => {
    fetchApprovedEvents();
  }, []);

  const fetchApprovedEvents = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/get_approved_requests.php`);
      const data = await response.json();
      if (data.success) {
        setEvents(data.events || []);
      }
      setLoading(false);
    } catch (error) {
      console.error('Error fetching events:', error);
      setError('Failed to load events');
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

      const hasEvent = events.some(event => {
        const eventDate = getEventDate(event);
        return eventDate &&
          eventDate.getDate() === i &&
          eventDate.getMonth() === month &&
          eventDate.getFullYear() === year;
      });

      dates.push({
        day: i,
        currentMonth: true,
        isToday: isToday,
        hasEvent: hasEvent
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
              ${dayObj.hasEvent ? 'has-event' : ''}
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
    <div className="approver-dashboard">
      <h1 className="page-title">APPROVER DASHBOARD</h1>
      
      <div className="dashboard-content">
        <div className="calendar-section">
          <div className="calendar-nav">
            <button onClick={() => handleNavClick("prev")} className="nav-button">
              <i className='bx bx-chevron-left'></i>
            </button>
            <h2>{months[month]} {year}</h2>
            <button onClick={() => handleNavClick("next")} className="nav-button">
              <i className='bx bx-chevron-right'></i>
            </button>
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
              {renderCalendar()}
            </tbody>
          </table>
        </div>

        <div className="events-section">
          <h3>Upcoming Events</h3>
          {loading ? (
            <p>Loading events...</p>
          ) : error ? (
            <p className="error">{error}</p>
          ) : upcomingEvents.length > 0 ? (
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
    </div>
  );
};

export default ApproverDashboard;