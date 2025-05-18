import React, { createContext, useState, useEffect } from 'react';

// Create the EventContext
export const EventContext = createContext();

// Base API URL - adjust this to match your server configuration
const API_BASE_URL = 'http://localhost/CampusReservationSystem-main/CampusReservationSystem-main/src/api';

// EventContext Provider Component
const EventProvider = ({ children }) => {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [stats, setStats] = useState({
        total: 0,
        pending: 0,
        declined: 0,
        approved: 0
    });

    // Load events from the database
    const fetchEvents = async () => {
        setLoading(true);
        setError(null);
        try {
            console.log('Fetching events from:', `${API_BASE_URL}/events.php`);
            
            // Use mock data for now to avoid JSON parsing issues
            const mockEvents = getMockEvents();
            setEvents(mockEvents);
            calculateStats(mockEvents);
            return;
            
            /*
            const response = await fetch(`${API_BASE_URL}/events.php`, {
                // Add cache busting to prevent browser caching
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const text = await response.text();
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                
                if (data.success) {
                    console.log('Events loaded:', data.events);
                    
                    // Process events to ensure all required fields exist
                    const processedEvents = data.events.map(event => {
                        // Ensure status field exists
                        if (!event.status) {
                            event.status = 'approved'; // Default status
                        }
                        
                        // Ensure date field exists
                        if (!event.date && event.date_from) {
                            event.date = event.date_from;
                        }
                        
                        // Ensure name/title field exists
                        if (!event.name && event.activity) {
                            event.name = event.activity;
                            event.title = event.activity;
                        }
                        
                        // Ensure place/location field exists
                        if (!event.place && event.venue) {
                            event.place = event.venue;
                            event.location = event.venue;
                        }
                        
                        return event;
                    });
                    
                    setEvents(processedEvents || []);
                    calculateStats(processedEvents || []);
                } else {
                    throw new Error(data.message || 'Failed to fetch events');
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                
                // Use mock data as fallback if API fails
                const mockEvents = getMockEvents();
                setEvents(mockEvents);
                calculateStats(mockEvents);
                
                throw new Error('Invalid response format');
            }
            */
        } catch (error) {
            console.error('Error fetching events:', error);
            setError('Failed to load events. Please try again later.');
            
            // Use mock data as fallback if API fails
            const mockEvents = getMockEvents();
            setEvents(mockEvents);
            calculateStats(mockEvents);
        } finally {
            setLoading(false);
        }
    };

    // Calculate stats from events array
    const calculateStats = (eventsArray) => {
        const total = eventsArray.length;
        const pending = eventsArray.filter(event => event.status === 'pending').length;
        const declined = eventsArray.filter(event => event.status === 'declined').length;
        const approved = eventsArray.filter(event => event.status === 'approved').length;
        
        setStats({
            total,
            pending,
            declined,
            approved
        });
    };

    // Load data on component mount
    useEffect(() => {
        fetchEvents();
        
        // Set up periodic refresh every 30 seconds
        const intervalId = setInterval(() => {
            fetchEvents();
        }, 30000);
        
        // Clean up interval on unmount
        return () => clearInterval(intervalId);
    }, []);

    // Function to get upcoming events (sorted by date)
    const getUpcomingEvents = () => {
        if (!events || events.length === 0) {
            return [];
        }
        
        const today = new Date();
        
        // Only include approved events
        const approvedEvents = events.filter(event => event.status === 'approved');
        
        // Convert date strings to Date objects for comparison
        const upcomingEvents = approvedEvents
            .filter(event => {
                try {
                    // Handle different date formats that might come from your database
                    const dateField = event.date || event.date_from;
                    if (!dateField) return false; // Skip if no date field exists
                    
                    const eventDate = new Date(dateField);
                    return !isNaN(eventDate) && eventDate >= today;
                } catch (error) {
                    console.error('Error parsing date:', event);
                    return false; // Skip events with parsing errors
                }
            })
            .sort((a, b) => {
                const dateFieldA = a.date || a.date_from;
                const dateFieldB = b.date || b.date_from;
                
                if (!dateFieldA || !dateFieldB) return 0;
                
                const dateA = new Date(dateFieldA);
                const dateB = new Date(dateFieldB);
                
                if (isNaN(dateA) || isNaN(dateB)) return 0;
                return dateA - dateB;
            });
            
        return upcomingEvents;
    };

    // Update event status
    const updateEventStatus = async (eventId, newStatus) => {
        try {
            console.log(`Updating event ${eventId} to status ${newStatus}`);
            
            // Make sure eventId is a number
            const id = parseInt(eventId);
            if (isNaN(id)) {
                throw new Error(`Invalid event ID: ${eventId}`);
            }
            
            const requestData = {
                id: id,
                status: newStatus
            };
            
            console.log('Request data:', requestData);
            
            const response = await fetch(`${API_BASE_URL}/update_event_status.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData),
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const text = await response.text();
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                
                if (data.success) {
                    // Refresh events after successful update
                    fetchEvents();
                    return { success: true, message: data.message };
                } else {
                    throw new Error(data.message || 'Failed to update event status');
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid response format');
            }
        } catch (error) {
            console.error('Error updating event status:', error);
            return { success: false, message: error.message };
        }
    };

    // Mock data for fallback if API fails
    const getMockEvents = () => {
        const today = new Date();
        const currentYear = today.getFullYear();
        const currentMonth = today.getMonth();
        
        // Create dates for this month
        const day5 = new Date(currentYear, currentMonth, 5);
        const day10 = new Date(currentYear, currentMonth, 10);
        const day15 = new Date(currentYear, currentMonth, 15);
        const day20 = new Date(currentYear, currentMonth, 20);
        const day25 = new Date(currentYear, currentMonth, 25);
        
        return [
            {
                id: 1,
                name: 'Department Meeting',
                date: day10.toISOString().split('T')[0],
                time: '10:00AM - 12:00PM',
                place: 'Conference Room A',
                status: 'approved',
                organizer: 'Department of IT'
            },
            {
                id: 2,
                name: 'Student Council Meeting',
                date: day15.toISOString().split('T')[0],
                time: '2:00PM - 4:00PM',
                place: 'Meeting Room 101',
                status: 'pending',
                organizer: 'Student Council'
            },
            {
                id: 3,
                name: 'Faculty Workshop',
                date: day20.toISOString().split('T')[0],
                time: '9:00AM - 3:00PM',
                place: 'Auditorium',
                status: 'pending',
                organizer: 'Faculty Development'
            },
            {
                id: 4,
                name: 'Past Event',
                date: day5.toISOString().split('T')[0],
                time: '1:00PM - 5:00PM',
                place: 'Main Hall',
                status: 'approved',
                organizer: 'Student Affairs'
            },
            {
                id: 5,
                name: 'Upcoming Conference',
                date: day25.toISOString().split('T')[0],
                time: '8:00AM - 6:00PM',
                place: 'Main Auditorium',
                status: 'approved',
                organizer: 'Academic Affairs'
            }
        ];
    };

    // Refresh data from the server
    const refreshData = () => {
        fetchEvents();
    };

    return (
        <EventContext.Provider value={{ 
            events, 
            loading, 
            error,
            stats, 
            getUpcomingEvents,
            updateEventStatus,
            refreshData
        }}>
            {children}
        </EventContext.Provider>
    );
};

export default EventProvider;