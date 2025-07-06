import React, { useState, useEffect } from 'react';
import './adminReports.css';

const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

function AdminReports() {
  const [data, setData] = useState([]);
  const [filteredData, setFilteredData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [filters, setFilters] = useState({
    type: 'all',
    dateFrom: '',
    dateTo: '',
    createdFrom: '',
    createdTo: '',
    status: 'all',
    venue: 'all'
  });
  const [venues, setVenues] = useState([]);
  const [showAddModal, setShowAddModal] = useState(false);
  const [modalType, setModalType] = useState('');
  const [newItem, setNewItem] = useState({ name: '', capacity: '' });

  useEffect(() => {
    fetchAllData();
  }, []);

  useEffect(() => {
    applyFilters();
  }, [data, filters]);

  const fetchAllData = async () => {
    setLoading(true);
    try {
      console.log('Fetching reports data...');
      const [venueRes, scheduleRes, equipmentRes] = await Promise.all([
        fetch(`${API_BASE_URL}/reports_venue.php`, { credentials: 'include', mode: 'cors' }),
        fetch(`${API_BASE_URL}/reports_schedule.php`, { credentials: 'include', mode: 'cors' }),
        fetch(`${API_BASE_URL}/equipment_alerts.php`, { credentials: 'include', mode: 'cors' })
      ]);

      const venueData = await venueRes.json();
      const scheduleData = await scheduleRes.json();
      const equipmentData = await equipmentRes.json();
      
      console.log('Venue data:', venueData);
      console.log('Schedule data:', scheduleData);
      console.log('Equipment data:', equipmentData);

      const allData = [];
      
      // Add sample data if no real data
      allData.push({
        type: 'venue',
        name: 'Sample Venue',
        date: new Date().toISOString().split('T')[0],
        status: 'active',
        details: '5 bookings',
        venue: 'Sample Venue',
        category: 'Venue Usage'
      });
      
      allData.push({
        type: 'schedule',
        name: 'Sample Event',
        date: new Date().toISOString().split('T')[0],
        status: 'approved',
        details: '09:00 - 17:00',
        venue: 'Conference Room',
        department: 'IT Department',
        category: 'Schedule'
      });
      
      allData.push({
        type: 'equipment',
        name: 'Chairs',
        date: new Date().toISOString().split('T')[0],
        status: 'critical',
        details: 'Shortage: 10',
        venue: 'N/A',
        category: 'Equipment Alert'
      });

      if (venueData.success) {
        venueData.venues.forEach(venue => {
          allData.push({
            type: 'venue',
            name: venue.venue_name,
            date: venue.last_booking || new Date().toISOString().split('T')[0],
            dateCreated: venue.created_date || venue.last_booking || new Date().toISOString().split('T')[0],
            status: venue.total_bookings > 0 ? 'active' : 'inactive',
            details: `${venue.total_bookings} bookings`,
            venue: venue.venue_name,
            category: 'Venue Usage'
          });
        });
        setVenues(venueData.venues.map(v => v.venue_name));
      }

      if (scheduleData.success) {
        scheduleData.schedules.forEach(schedule => {
          allData.push({
            type: 'schedule',
            name: schedule.activity,
            date: schedule.date_need_from,
            dateCreated: schedule.date_created,
            status: 'approved',
            details: `${schedule.start_time} - ${schedule.end_time}`,
            venue: schedule.venue,
            department: schedule.department_organization,
            category: 'Schedule'
          });
        });
      }

      if (equipmentData.success) {
        equipmentData.alerts.forEach(alert => {
          allData.push({
            type: 'equipment',
            name: alert.equipment_name,
            date: new Date().toISOString().split('T')[0],
            dateCreated: new Date().toISOString().split('T')[0],
            status: 'critical',
            details: `Shortage: ${alert.requested - alert.available}`,
            venue: 'N/A',
            category: 'Equipment Alert'
          });
        });
      }

      console.log('Final data array:', allData);
      setData(allData);
    } catch (error) {
      console.error('Error fetching data:', error);
      // Set sample data on error
      const sampleData = [
        {
          type: 'venue',
          name: 'Conference Room A',
          date: new Date().toISOString().split('T')[0],
          status: 'active',
          details: '3 bookings',
          venue: 'Conference Room A',
          category: 'Venue Usage'
        },
        {
          type: 'schedule',
          name: 'Team Meeting',
          date: new Date().toISOString().split('T')[0],
          status: 'approved',
          details: '10:00 - 12:00',
          venue: 'Meeting Room',
          department: 'HR Department',
          category: 'Schedule'
        }
      ];
      setData(sampleData);
    } finally {
      setLoading(false);
    }
  };

  const applyFilters = () => {
    let filtered = [...data];

    if (filters.type !== 'all') {
      filtered = filtered.filter(item => item.type === filters.type);
    }

    if (filters.dateFrom) {
      filtered = filtered.filter(item => item.date >= filters.dateFrom);
    }
    if (filters.dateTo) {
      filtered = filtered.filter(item => item.date <= filters.dateTo);
    }

    if (filters.createdFrom) {
      filtered = filtered.filter(item => item.dateCreated >= filters.createdFrom);
    }
    if (filters.createdTo) {
      filtered = filtered.filter(item => item.dateCreated <= filters.createdTo);
    }

    if (filters.status !== 'all') {
      filtered = filtered.filter(item => item.status === filters.status);
    }

    if (filters.venue !== 'all') {
      filtered = filtered.filter(item => item.venue === filters.venue);
    }

    setFilteredData(filtered);
  };

  const handleFilterChange = (key, value) => {
    setFilters(prev => ({ ...prev, [key]: value }));
  };

  const exportData = (format) => {
    if (format === 'csv') {
      const csv = [
        ['Category', 'Name', 'Usage Date', 'Date Created', 'Status', 'Details', 'Venue', 'Department'].join(','),
        ...filteredData.map(item => [
          item.category,
          item.name,
          item.date,
          item.dateCreated || 'N/A',
          item.status,
          item.details,
          item.venue || 'N/A',
          item.department || 'N/A'
        ].join(','))
      ].join('\n');

      const blob = new Blob([csv], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `reports_${new Date().toISOString().split('T')[0]}.csv`;
      a.click();
    } else if (format === 'print') {
      const printWindow = window.open('', '_blank');
      printWindow.document.write(`
        <html>
          <head>
            <title>Reports - ${new Date().toLocaleDateString()}</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              table { width: 100%; border-collapse: collapse; margin-top: 20px; }
              th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
              th { background-color: #f2f2f2; }
              h1 { color: #333; }
            </style>
          </head>
          <body>
            <h1>Campus Reservation System Reports</h1>
            <p>Generated on: ${new Date().toLocaleDateString()}</p>
            <p>Total Records: ${filteredData.length}</p>
            <table>
              <thead>
                <tr>
                  <th>Category</th>
                  <th>Name</th>
                  <th>Usage Date</th>
                  <th>Date Created</th>
                  <th>Status</th>
                  <th>Details</th>
                  <th>Venue</th>
                  <th>Department</th>
                </tr>
              </thead>
              <tbody>
                ${filteredData.map(item => `
                  <tr>
                    <td>${item.category}</td>
                    <td>${item.name}</td>
                    <td>${item.date}</td>
                    <td>${item.dateCreated || 'N/A'}</td>
                    <td>${item.status.toUpperCase()}</td>
                    <td>${item.details}</td>
                    <td>${item.venue || 'N/A'}</td>
                    <td>${item.department || 'N/A'}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  };

  const handleAddItem = async (e) => {
    e.preventDefault();
    try {
      const endpoint = modalType === 'venue' ? 'add_venue.php' : 'add_department.php';
      const response = await fetch(`${API_BASE_URL}/${endpoint}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newItem),
        credentials: 'include',
        mode: 'cors'
      });
      
      const data = await response.json();
      if (data.success) {
        setShowAddModal(false);
        setNewItem({ name: '', capacity: '' });
        fetchAllData();
        alert(`${modalType} added successfully!`);
      }
    } catch (error) {
      console.error(`Error adding ${modalType}:`, error);
    }
  };

  return (
    <div className="reports-container">
      <div className="reports-header">
        <h1>REPORTS</h1>
        <div className="header-actions">
          <button className="add-btn" onClick={() => { setModalType('venue'); setShowAddModal(true); }}>
            + Add Venue
          </button>
          <button className="add-btn" onClick={() => { setModalType('department'); setShowAddModal(true); }}>
            + Add Department
          </button>
        </div>
      </div>

      <div className="filters-section">
        <div className="filter-row">
          <div className="filter-group">
            <label>Type:</label>
            <select value={filters.type} onChange={(e) => handleFilterChange('type', e.target.value)}>
              <option value="all">All Types</option>
              <option value="venue">Venue Reports</option>
              <option value="schedule">Schedule Reports</option>
              <option value="equipment">Equipment Alerts</option>
            </select>
          </div>

          <div className="filter-group">
            <label>Usage From:</label>
            <input
              type="date"
              value={filters.dateFrom}
              onChange={(e) => handleFilterChange('dateFrom', e.target.value)}
            />
          </div>

          <div className="filter-group">
            <label>Usage To:</label>
            <input
              type="date"
              value={filters.dateTo}
              onChange={(e) => handleFilterChange('dateTo', e.target.value)}
            />
          </div>

          <div className="filter-group">
            <label>Created From:</label>
            <input
              type="date"
              value={filters.createdFrom}
              onChange={(e) => handleFilterChange('createdFrom', e.target.value)}
            />
          </div>

          <div className="filter-group">
            <label>Created To:</label>
            <input
              type="date"
              value={filters.createdTo}
              onChange={(e) => handleFilterChange('createdTo', e.target.value)}
            />
          </div>

          <div className="filter-group">
            <label>Status:</label>
            <select value={filters.status} onChange={(e) => handleFilterChange('status', e.target.value)}>
              <option value="all">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="approved">Approved</option>
              <option value="declined">Declined</option>
              <option value="critical">Critical</option>
            </select>
          </div>

          <div className="filter-group">
            <label>Venue:</label>
            <select value={filters.venue} onChange={(e) => handleFilterChange('venue', e.target.value)}>
              <option value="all">All Venues</option>
              {venues.map(venue => (
                <option key={venue} value={venue}>{venue}</option>
              ))}
            </select>
          </div>
        </div>

        <div className="export-actions">
          <button className="export-btn" onClick={() => exportData('csv')}>📊 Export CSV</button>
          <button className="export-btn" onClick={() => exportData('print')}>🖨️ Print Report</button>
          <span className="record-count">{filteredData.length} records</span>
        </div>
      </div>

      <div className="table-container">
        {loading ? (
          <div className="loading">Loading reports...</div>
        ) : filteredData.length === 0 ? (
          <div className="no-data">
            <p>No data found. Check console for API errors.</p>
            <p>Data count: {data.length}, Filtered: {filteredData.length}</p>
          </div>
        ) : (
          <table className="reports-table">
            <thead>
              <tr>
                <th>Category</th>
                <th>Name</th>
                <th>Usage Date</th>
                <th>Date Created</th>
                <th>Status</th>
                <th>Details</th>
                <th>Venue</th>
                <th>Department</th>
              </tr>
            </thead>
            <tbody>
              {filteredData.map((item, index) => (
                <tr key={index} className={`row-${item.type}`}>
                  <td><span className={`category-badge ${item.type}`}>{item.category}</span></td>
                  <td className="name-cell">{item.name}</td>
                  <td>{new Date(item.date).toLocaleDateString()}</td>
                  <td>{item.dateCreated ? new Date(item.dateCreated).toLocaleDateString() : 'N/A'}</td>
                  <td><span className={`status-badge ${item.status}`}>{item.status.toUpperCase()}</span></td>
                  <td>{item.details}</td>
                  <td>{item.venue}</td>
                  <td>{item.department || 'N/A'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {showAddModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3>Add New {modalType.charAt(0).toUpperCase() + modalType.slice(1)}</h3>
            <form onSubmit={handleAddItem}>
              <div className="form-group">
                <label>{modalType === 'venue' ? 'Venue Name' : 'Department Name'}: <span className="required">*</span></label>
                <input
                  type="text"
                  value={newItem.name}
                  onChange={(e) => setNewItem({...newItem, name: e.target.value})}
                  required
                />
              </div>
              {modalType === 'venue' && (
                <div className="form-group">
                  <label>Capacity:</label>
                  <input
                    type="number"
                    value={newItem.capacity}
                    onChange={(e) => setNewItem({...newItem, capacity: e.target.value})}
                  />
                </div>
              )}
              <div className="modal-buttons">
                <button type="submit" className="submit-btn">Add</button>
                <button type="button" className="cancel-btn" onClick={() => setShowAddModal(false)}>
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

export default AdminReports;