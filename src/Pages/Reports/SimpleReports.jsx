import React, { useState, useEffect } from 'react';
import './Reports.css';

const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

const SimpleReports = () => {
  const [reportData, setReportData] = useState({
    totalEvents: 0,
    approvedEvents: 0,
    pendingEvents: 0,
    declinedEvents: 0,
    upcomingEvents: 0,
    mostUsedVenue: '',
    mostActiveMonth: '',
    loading: true,
    error: null
  });

  useEffect(() => {
    fetchReportData();
  }, []);

  const fetchReportData = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/stats_with_approved.php`);
      
      const text = await response.text();
      let data;
      try {
        const cleanedText = text.replace(/\?>[\s\S]*$/, '');
        data = JSON.parse(cleanedText);
      } catch (e) {
        console.error("Invalid JSON response:", text);
        throw new Error("Server returned invalid JSON response");
      }

      if (data.status === 'success') {
        setReportData({
          totalEvents: data.total_events || 0,
          approvedEvents: data.approved_events || 0,
          pendingEvents: data.pending_events || 0,
          declinedEvents: data.declined_events || 0,
          upcomingEvents: data.upcoming_events || 0,
          mostUsedVenue: data.most_used_venue || 'N/A',
          mostActiveMonth: data.most_active_month || 'N/A',
          loading: false,
          error: null
        });
      } else {
        throw new Error(data.message || 'Failed to fetch report data');
      }
    } catch (error) {
      console.error("Error fetching report data:", error);
      setReportData(prev => ({
        ...prev,
        loading: false,
        error: 'Failed to load report data. Please try again later.'
      }));
    }
  };

  const exportToTXT = () => {
    window.open(`${API_BASE_URL}/export_pdf.php`, '_blank');
  };

  // Calculate percentages for the status chart
  const total = reportData.approvedEvents + reportData.pendingEvents + reportData.declinedEvents;
  const approvedPercentage = total > 0 ? Math.round((reportData.approvedEvents / total) * 100) : 0;
  const pendingPercentage = total > 0 ? Math.round((reportData.pendingEvents / total) * 100) : 0;
  const declinedPercentage = total > 0 ? Math.round((reportData.declinedEvents / total) * 100) : 0;

  return (
    <div className="reports-container">
      <div className="reports-header">
        <h1 className="page-title">REPORTS</h1>
        <div className="header-buttons">
          <button 
            className="export-button"
            onClick={exportToTXT}
          >
            Export Report
          </button>
          <button 
            className="refresh-button"
            onClick={fetchReportData}
            disabled={reportData.loading}
          >
            {reportData.loading ? 'Loading...' : 'Refresh'}
          </button>
        </div>
      </div>

      {reportData.error && (
        <div className="error-message">
          <p>{reportData.error}</p>
        </div>
      )}

      {reportData.loading ? (
        <p className="loading-message">Loading report data...</p>
      ) : (
        <>
          <div className="reports-grid">
            <div className="report-card total-events">
              <h2>Total Events</h2>
              <p className="report-number">{reportData.totalEvents}</p>
            </div>
            <div className="report-card approved-events">
              <h2>Approved Events</h2>
              <p className="report-number">{reportData.approvedEvents}</p>
            </div>
            <div className="report-card pending-events">
              <h2>Pending Events</h2>
              <p className="report-number">{reportData.pendingEvents}</p>
            </div>
            <div className="report-card declined-events">
              <h2>Declined Events</h2>
              <p className="report-number">{reportData.declinedEvents}</p>
            </div>
            <div className="report-card upcoming-events">
              <h2>Upcoming Events</h2>
              <p className="report-number">{reportData.upcomingEvents}</p>
            </div>
            <div className="report-card most-used-venue">
              <h2>Most Used Venue</h2>
              <p className="report-text">{reportData.mostUsedVenue}</p>
            </div>
            <div className="report-card most-active-month">
              <h2>Most Active Month</h2>
              <p className="report-text">{reportData.mostActiveMonth}</p>
            </div>
          </div>

          {/* Simple visual representation of request status distribution */}
          <div className="chart-section">
            <h2>Request Status Distribution</h2>
            <div className="status-chart">
              <div className="status-bar">
                <div 
                  className="status-segment approved" 
                  style={{width: `${approvedPercentage}%`}}
                  title={`Approved: ${reportData.approvedEvents} (${approvedPercentage}%)`}
                >
                  {approvedPercentage > 10 ? `${approvedPercentage}%` : ''}
                </div>
                <div 
                  className="status-segment pending" 
                  style={{width: `${pendingPercentage}%`}}
                  title={`Pending: ${reportData.pendingEvents} (${pendingPercentage}%)`}
                >
                  {pendingPercentage > 10 ? `${pendingPercentage}%` : ''}
                </div>
                <div 
                  className="status-segment declined" 
                  style={{width: `${declinedPercentage}%`}}
                  title={`Declined: ${reportData.declinedEvents} (${declinedPercentage}%)`}
                >
                  {declinedPercentage > 10 ? `${declinedPercentage}%` : ''}
                </div>
              </div>
              <div className="status-legend">
                <div className="legend-item">
                  <div className="legend-color approved"></div>
                  <div className="legend-label">Approved ({reportData.approvedEvents})</div>
                </div>
                <div className="legend-item">
                  <div className="legend-color pending"></div>
                  <div className="legend-label">Pending ({reportData.pendingEvents})</div>
                </div>
                <div className="legend-item">
                  <div className="legend-color declined"></div>
                  <div className="legend-label">Declined ({reportData.declinedEvents})</div>
                </div>
              </div>
            </div>
          </div>

          {/* Monthly Requests Chart (CSS-based) */}
          <div className="chart-section">
            <h2>Monthly Requests</h2>
            <div className="monthly-chart">
              <div className="month-bar">
                <div className="month-item">
                  <div className="month-bar-value" style={{height: '40%'}}>
                    <span className="month-value">2</span>
                  </div>
                  <div className="month-label">Jan</div>
                </div>
                <div className="month-item">
                  <div className="month-bar-value" style={{height: '60%'}}>
                    <span className="month-value">3</span>
                  </div>
                  <div className="month-label">Feb</div>
                </div>
                <div className="month-item">
                  <div className="month-bar-value" style={{height: '80%'}}>
                    <span className="month-value">4</span>
                  </div>
                  <div className="month-label">Mar</div>
                </div>
                <div className="month-item">
                  <div className="month-bar-value" style={{height: '70%'}}>
                    <span className="month-value">3.5</span>
                  </div>
                  <div className="month-label">Apr</div>
                </div>
                <div className="month-item">
                  <div className="month-bar-value" style={{height: '90%'}}>
                    <span className="month-value">4.5</span>
                  </div>
                  <div className="month-label">May</div>
                </div>
                <div className="month-item">
                  <div className="month-bar-value" style={{height: '100%'}}>
                    <span className="month-value">5</span>
                  </div>
                  <div className="month-label">Jun</div>
                </div>
              </div>
            </div>
          </div>

          {/* Department Requests Chart (CSS-based) */}
          <div className="chart-section">
            <h2>Requests by Department</h2>
            <div className="dept-chart">
              <div className="dept-item">
                <div className="dept-name">Computer Studies</div>
                <div className="dept-bar-container">
                  <div className="dept-bar" style={{width: '90%'}}>
                    <span className="dept-value">9</span>
                  </div>
                </div>
              </div>
              <div className="dept-item">
                <div className="dept-name">Accountancy</div>
                <div className="dept-bar-container">
                  <div className="dept-bar" style={{width: '30%'}}>
                    <span className="dept-value">3</span>
                  </div>
                </div>
              </div>
              <div className="dept-item">
                <div className="dept-name">Education</div>
                <div className="dept-bar-container">
                  <div className="dept-bar" style={{width: '20%'}}>
                    <span className="dept-value">2</span>
                  </div>
                </div>
              </div>
              <div className="dept-item">
                <div className="dept-name">Hotel Management</div>
                <div className="dept-bar-container">
                  <div className="dept-bar" style={{width: '10%'}}>
                    <span className="dept-value">1</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </>
      )}
    </div>
  );
};

export default SimpleReports;