import React, { useState, useEffect, useContext } from 'react';
import { EventContext } from '../../context/EventContext';

import 'boxicons/css/boxicons.min.css';
import './clientDashboard.css';

// Base API URL
const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

// Helper function to render icons with fallback
const Icon = ({ iconClass }) => {
  const [iconsLoaded, setIconsLoaded] = useState(true);
  
  useEffect(() => {
    // Check if Boxicons is loaded
    const testIcon = document.createElement('i');
    testIcon.className = 'bx bx-menu';
    document.body.appendChild(testIcon);
    
    const computedStyle = window.getComputedStyle(testIcon);
    const isLoaded = computedStyle.fontFamily.includes('boxicons') || 
                    computedStyle.fontFamily.includes('BoxIcons');
    
    document.body.removeChild(testIcon);
    setIconsLoaded(isLoaded);
  }, []);
  
  if (iconsLoaded) {
    return <i className={`bx ${iconClass}`}></i>;
  } else {
    // Map to Font Awesome icons as fallback
    const iconMap = {
      'bx-chevron-left': 'fa-solid fa-chevron-left',
      'bx-chevron-right': 'fa-solid fa-chevron-right',
      'bx-search': 'fa-solid fa-magnifying-glass'
    };
    return <i className={iconMap[iconClass] || 'fa-solid fa-circle'}></i>;
  }
};

function ClientDashboard({ isCollapsed }) {
  const { events, loading, error, refreshData } = useContext(EventContext);
  const [date] = useState(new Date());
  const [month, setMonth] = useState(date.getMonth());
  const [year, setYear] = useState(date.getFullYear());
  const [searchTerm, setSearchTerm] = useState('');
  const [goToDate, setGoToDate] = useState('');
  const [showGoToModal, setShowGoToModal] = useState(false);
  const [goToMonth, setGoToMonth] = useState('');
  const [goToDay, setGoToDay] = useState('');
  const [goToYear, setGoToYear] = useState('');
  const [filteredEvents, setFilteredEvents] = useState([]);
  const [showEventModal, setShowEventModal] = useState(false);
  const [selectedDayEvents, setSelectedDayEvents] = useState([]);

  const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];

  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  const shortDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  // Fetch approved events directly from the approved_request table
  useEffect(() => {
    const fetchApprovedRequests = async () => {
      try {
        const response = await fetch(`${API_BASE_URL}/admin_dashboard_approved_events.php`, {
          credentials: 'include',
          mode: 'cors'
        });
        const data = await response.json();
        if (data.success) {
          console.log('Approved events loaded:', data.events);
          
          // Process events to ensure we have all required fields
          const processedEvents = data.events.map(event => ({
            ...event,
            // Ensure department is available from department_organization
            department: event.department_organization || event.department,
            // Ensure organizer is available from requested_by
            organizer: event.request_by || event.requestor_name || event.organizer,
            // Ensure we have proper time format
            start_time: event.start_time || event.time_need_from,
            end_time: event.end_time || event.time_need_until
          }));
          
          setFilteredEvents(processedEvents || []);
        } else {
          console.error("Failed to load approved events:", data.message);
          // Fallback to original calendar events endpoint
          const fallbackResponse = await fetch(`${API_BASE_URL}/admin_dashboard_approved_events.php`, {
            credentials: 'include',
            mode: 'cors'
          });
          const fallbackData = await fallbackResponse.json();
          if (fallbackData.success) {
            setFilteredEvents(fallbackData.events || []);
          }
        }
      } catch (error) {
        console.error("Error fetching approved events:", error);
        // Fallback to original calendar events endpoint
        try {
          const fallbackResponse = await fetch(`${API_BASE_URL}/calendar_events.php`, {
            credentials: 'include',
            mode: 'cors'
          });
          const fallbackData = await fallbackResponse.json();
          if (fallbackData.success) {
            setFilteredEvents(fallbackData.events || []);
          }
        } catch (fallbackError) {
          console.error("Error fetching calendar events:", fallbackError);
        }
      }
    };
    
    fetchApprovedRequests();
  }, []);

  const renderCalendar = () => {
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    let dates = [];
    const totalCells = 7 * 6; // 7 days x 6 weeks

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

      // Check if there are events on this day
      const hasUpcomingEvent = upcomingEvents.some(event => {
        const eventDate = getEventDate(event);
        return eventDate &&
          eventDate.getDate() === i &&
          eventDate.getMonth() === month &&
          eventDate.getFullYear() === year;
      });

      const hasFinishedEvent = finishedEvents.some(event => {
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
        hasUpcomingEvent: hasUpcomingEvent,
        hasFinishedEvent: hasFinishedEvent
      });
    }

    // Next month days (to fill remaining cells)
    const remainingCells = totalCells - dates.length;
    for (let i = 1; i <= remainingCells; i++) {
      dates.push({
        day: i,
        currentMonth: false,
        isToday: false
      });
    }

    // Split into weeks (6 weeks)
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
              ${dayObj.hasUpcomingEvent ? 'has-upcoming-event' : ''} 
              ${dayObj.hasFinishedEvent ? 'has-finished-event' : ''}
            `}
            onClick={() => {
              if (dayObj.currentMonth) {
                const dayEvents = filteredEvents.filter(event => {
                  const eventDate = getEventDate(event);
                  return eventDate && 
                    eventDate.getDate() === dayObj.day && 
                    eventDate.getMonth() === month && 
                    eventDate.getFullYear() === year;
                });
                setSelectedDayEvents(dayEvents);
                setShowEventModal(dayEvents.length > 0);
              }
            }}
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

  const handleSearch = (e) => {
    e.preventDefault();
    if (!searchTerm.trim()) {
      // Reset to only approved events
      const fetchApprovedRequests = async () => {
        try {
          const response = await fetch(`${API_BASE_URL}/admin_dashboard_approved_events.php`, {
            credentials: 'include',
            mode: 'cors'
          });
          const data = await response.json();
          if (data.success) {
            setFilteredEvents(data.events || []);
          } else {
            // Fallback to original calendar events endpoint
            const fallbackResponse = await fetch(`${API_BASE_URL}/calendar_events.php`, {
              credentials: 'include',
              mode: 'cors'
            });
            const fallbackData = await fallbackResponse.json();
            if (fallbackData.success) {
              setFilteredEvents(fallbackData.events || []);
            }
          }
        } catch (error) {
          console.error("Error fetching approved events:", error);
          // Try fallback
          try {
            const fallbackResponse = await fetch(`${API_BASE_URL}/calendar_events.php`, {
              credentials: 'include',
              mode: 'cors'
            });
            const fallbackData = await fallbackResponse.json();
            if (fallbackData.success) {
              setFilteredEvents(fallbackData.events || []);
            }
          } catch (fallbackError) {
            console.error("Error fetching calendar events:", fallbackError);
          }
        }
      };
      
      fetchApprovedRequests();
      return;
    }

    // Search only within approved events
    const filtered = filteredEvents.filter(event => {
      const title = event.activity || event.event_name || event.name || event.title || '';
      return title.toLowerCase().includes(searchTerm.toLowerCase());
    });
    setFilteredEvents(filtered);
  };
  
  const handleModalGoTo = (e) => {
    e.preventDefault();
    if (!goToMonth || !goToYear) return;
    
    const month = parseInt(goToMonth) - 1;
    const year = parseInt(goToYear);
    
    if (!isNaN(month) && !isNaN(year) && month >= 0 && month <= 11) {
      setMonth(month);
      setYear(year);
      setShowGoToModal(false);
      setGoToMonth('');
      setGoToDay('');
      setGoToYear('');
    }
  };

  const handleTextGoTo = (e) => {
    e.preventDefault();
    if (!goToDate) return;
    
    const dateParts = goToDate.split('-');
    if (dateParts.length === 2) {
      const month = parseInt(dateParts[0]) - 1;
      const year = parseInt(dateParts[1]);
      
      if (!isNaN(month) && !isNaN(year) && month >= 0 && month <= 11) {
        setMonth(month);
        setYear(year);
        setGoToDate('');
      }
    } else if (dateParts.length === 3) {
      const month = parseInt(dateParts[0]) - 1;
      const year = parseInt(dateParts[2]);
      
      if (!isNaN(month) && !isNaN(year) && month >= 0 && month <= 11) {
        setMonth(month);
        setYear(year);
        setGoToDate('');
      }
    }
  };

  // Helper function to get event date
  const getEventDate = (event) => {
    try {
      // Try different date fields in order of preference
      const dateStr = event.date_need_from || event.date || event.start_time || '';
      if (!dateStr) return null;

      return new Date(dateStr);
    } catch (e) {
      console.error('Error parsing date:', e);
      return null;
    }
  };
  
  // Helper function to format time from database to AM/PM format
  const formatEventTime = (event) => {
    const formatTimeToAMPM = (timeStr) => {
      if (!timeStr) return '';
      const [hours, minutes] = timeStr.split(':');
      const hour = parseInt(hours);
      const ampm = hour >= 12 ? 'PM' : 'AM';
      const formattedHour = hour % 12 || 12;
      return `${formattedHour}:${minutes} ${ampm}`;
    };

    if (event.start_time && event.end_time) {
      return `${formatTimeToAMPM(event.start_time)} - ${formatTimeToAMPM(event.end_time)}`;
    }
    
    if (event.time_need_from && event.time_need_until) {
      return `${formatTimeToAMPM(event.time_need_from)} - ${formatTimeToAMPM(event.time_need_until)}`;
    }
    
    return event.time ? formatTimeToAMPM(event.time) : 'N/A';
  };

  // Filter events for current month
  const currentMonthEvents = filteredEvents.filter(event => {
    const eventDate = getEventDate(event);
    return eventDate &&
      eventDate.getMonth() === month &&
      eventDate.getFullYear() === year;
  });

  // Separate upcoming and finished events
  const today = new Date();
  today.setHours(0, 0, 0, 0); // Set to start of day for proper comparison
  
  const upcomingEvents = filteredEvents.filter(event => {
    try {
      const eventDate = new Date(event.date_need_from || event.date);
      eventDate.setHours(0, 0, 0, 0);
      return eventDate >= today;
    } catch (e) {
      console.error('Error filtering upcoming event:', e, event);
      return false;
    }
  });

  const finishedEvents = filteredEvents.filter(event => {
    try {
      const eventDate = new Date(event.date_need_from || event.date);
      eventDate.setHours(0, 0, 0, 0);
      return eventDate < today;
    } catch (e) {
      console.error('Error filtering finished event:', e, event);
      return false;
    }
  });

  // Current month events for display in the list
  const currentMonthUpcomingEvents = upcomingEvents.filter(event => {
    const eventDate = getEventDate(event);
    return eventDate &&
      eventDate.getMonth() === month &&
      eventDate.getFullYear() === year;
  });

  const currentMonthFinishedEvents = finishedEvents.filter(event => {
    const eventDate = getEventDate(event);
    return eventDate &&
      eventDate.getMonth() === month &&
      eventDate.getFullYear() === year;
  });

  // Format date for display
  const formatEventDate = (event) => {
    const eventDate = getEventDate(event);
    if (!eventDate) return 'Date TBD';

    return `${months[eventDate.getMonth()].slice(0, 3)} ${eventDate.getDate()}`;
  };

  return (
    <div className={`dashboard-container ${isCollapsed ? 'collapsed' : ''}`}>
      <div className="calendar-container">
        <h1 className='client-dashboard-container'>CALENDAR</h1>
        
        <div className="calendar-nav">
          <button onClick={() => handleNavClick("prev")} className="nav-button">
            <i className='bx bx-chevron-left'></i>
          </button>
          <h2>{months[month].toUpperCase()} {year}</h2>
          <button onClick={() => handleNavClick("next")} className="nav-button">
            <i className='bx bx-chevron-right'></i>
          </button>
        </div>
        <div className='search-container'>
          <button className='btn-goto' onClick={() => setShowGoToModal(true)}>GO TO</button>
          <form onSubmit={handleSearch} className="btn-search-container">
            <input 
              type="text" 
              placeholder="Search events..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
            <button type="submit" className='btn-search'>
              <i className="bx bx-search"></i>
            </button>
          </form>
        </div>

        <table className="calendar-table">
          <thead>
            <tr>
              {shortDays.map(day => (
                <th key={day}>{day.toUpperCase()}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {renderCalendar()}
          </tbody>
        </table>
      </div>

      <div className="event-planner">
        <div className="legend">
          <h4 className='dashboard-label'>LEGEND</h4>
          <div className="border">
            <button className="upcoming"> UPCOMING EVENTS </button>
            <button className="finished"> FINISHED EVENTS </button>
          </div>
        </div>
        <div className="events">
          <h4 className='dashboard-label'>UPCOMING EVENTS FOR <span className='dashboard-label'>{months[month].toUpperCase()} {year}</span></h4>
          <div className="border">
            {loading ? (
              <p className="loading">Loading events...</p>
            ) : error ? (
              <p className="error">{error}</p>
            ) : currentMonthUpcomingEvents.length > 0 ? (
              <ul className="event-list">
                {currentMonthUpcomingEvents.map((event, index) => (
                  <li key={`upcoming-${event.id || event.reference_number || Math.random()}-${index}`} className="event-item upcoming">
                    <span className="event-date">
                      {formatEventDate(event)}
                    </span>
                    <br />
                    <span className="event-title">{event.name || event.title || event.activity || 'Untitled Event'}</span>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="no-events">No upcoming events this month</p>
            )}
          </div>
          
          <h4 className='dashboard-label'>FINISHED EVENTS</h4>
          <div className="border">
            {loading ? (
              <p className="loading-message">Loading events...</p>
            ) : error ? (
              <p className="error-message">{error}</p>
            ) : currentMonthFinishedEvents.length > 0 ? (
              <ul className="event-list">
                {currentMonthFinishedEvents.map((event, index) => (
                  <li key={`finished-${event.id || event.reference_number || Math.random()}-${index}`} className="event-item finished">
                    <span className="event-date">
                      {formatEventDate(event)}
                    </span>
                    <br />
                    <span className="event-title">{event.name || event.title || event.activity || 'Untitled Event'}</span>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="no-events">No finished events this month</p>
            )}
          </div>
        </div>
      </div>

      {/* Go To Modal */}
      {showGoToModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3 className='modal-title-page'>GO TO</h3>
            <div className="modal-input-group">
              <label>MONTH</label>
              <input 
                type="number" 
                min="1" 
                max="12" 
                value={goToMonth}
                onChange={(e) => setGoToMonth(e.target.value)}
                placeholder="MM"
              />
            </div>
            <div className="modal-input-group">
              <label>DAY</label>
              <input 
                type="number" 
                min="1" 
                max="31" 
                value={goToDay}
                onChange={(e) => setGoToDay(e.target.value)}
                placeholder="DD"
              />
            </div>
            <div className="modal-input-group">
              <label>YEAR</label>
              <input 
                type="number" 
                min="2000" 
                max="2100" 
                value={goToYear}
                onChange={(e) => setGoToYear(e.target.value)}
                placeholder="YYYY"
              />
            </div>
            <div className="modal-buttons">
              <button 
                className="modal-ok" 
                onClick={handleModalGoTo}
                disabled={!goToMonth || !goToYear}
              >
                OK
              </button>
              <button 
                className="modal-cancel" 
                onClick={() => setShowGoToModal(false)}
              >
                CANCEL
              </button>
            </div>
          </div>
        </div>
      )}
      
      {/* Event Info Modal - Simplified version without edit features */}
      {showEventModal && (
        <div className="modal-overlay">
          <div className="modal-content event-info-modal">
            <h3 className='modal-title-page'>EVENTS ON {months[month]} {selectedDayEvents.length > 0 && getEventDate(selectedDayEvents[0])?.getDate()}, {year}</h3>
            
            <div className="event-modal-content">
              {selectedDayEvents.map((event, index) => (
                <div key={event.id || `modal-event-${index}`} className="event-modal-item">
                  <div className="event-modal-header">
                    <h4 className="event-name">{(event.activity || event.event_name || event.name || 'Untitled Event').toUpperCase()}</h4>
                  </div>
                  <div className="event-modal-details">
                    <p><strong>REFERENCE NO:</strong> {event.reference_number || 'N/A'}</p>
                    <p><strong>DATE CREATED:</strong> {event.approved_at || event.date_created || 'N/A'}</p>
                    <p><strong>DEPARTMENT:</strong> {event.department_organization || event.department || 'N/A'}</p>
                    <p><strong>LOCATION:</strong> {event.venue_name || event.venue || event.place || event.location || 'N/A'}</p>
                    <p><strong>DATE:</strong> {event.date_need_from ? 
                      `${new Date(event.date_need_from).toLocaleDateString()}` : 
                      (event.date ? new Date(event.date).toLocaleDateString() : 'N/A')}</p>
                    <p><strong>TIME:</strong> {formatEventTime(event)}</p>
                    <p><strong>PURPOSE:</strong> {event.purpose || 'N/A'}</p>
                    <p><strong>ORGANIZER:</strong> {event.request_by || event.requestor_name || event.organizer || 'N/A'}</p>
                  </div>
                  {index < selectedDayEvents.length - 1 && <hr />}
                </div>
              ))}
            </div>
            
            <div className="modal-buttons">
              <button
                className="modal-ok"
                onClick={() => setShowEventModal(false)}
              >
                CLOSE
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default ClientDashboard;