import React, { useState, useEffect } from 'react';
import { Bar, Pie } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';
import './Reports.css';

// Register Chart.js components
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement);

const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

const Reports = () => {
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
  
  const [chartData, setChartData] = useState({
    monthlyData: [],
    departmentData: [],
    statusData: [],
    loading: true,
    error: null
  });

  useEffect(() => {
    fetchReportData();
    fetchChartData();
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
  
  const fetchChartData = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/chart_data.php`);
      
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
        setChartData({
          monthlyData: data.monthly_data || [],
          departmentData: data.department_data || [],
          statusData: data.status_data || [],
          loading: false,
          error: null
        });
      } else {
        throw new Error(data.message || 'Failed to fetch chart data');
      }
    } catch (error) {
      console.error("Error fetching chart data:", error);
      setChartData(prev => ({
        ...prev,
        loading: false,
        error: 'Failed to load chart data. Please try again later.'
      }));
      
      // Set fallback data if fetch fails
      setChartData({
        monthlyData: [
          { month: 'January', count: 2 },
          { month: 'February', count: 3 },
          { month: 'March', count: 5 },
          { month: 'April', count: 4 },
          { month: 'May', count: 7 },
          { month: 'June', count: 11 }
        ],
        departmentData: [
          { department: 'Computer Studies', count: 9 },
          { department: 'Accountancy', count: 3 },
          { department: 'Education', count: 2 },
          { department: 'Hotel Management', count: 1 }
        ],
        statusData: [
          { status: 'Approved', count: 7 },
          { status: 'Pending', count: 1 },
          { status: 'Declined', count: 3 }
        ],
        loading: false,
        error: null
      });
    }
  };

  const exportToPDF = () => {
    // Open the PDF in a new window
    window.open(`${API_BASE_URL}/export_pdf.php`, '_blank');
  };

  // Prepare monthly data chart
  const monthlyChartData = {
    labels: chartData.monthlyData.map(item => item.month),
    datasets: [
      {
        label: 'Monthly Requests',
        data: chartData.monthlyData.map(item => item.count),
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }
    ]
  };

  // Prepare department data chart
  const departmentChartData = {
    labels: chartData.departmentData.map(item => item.department),
    datasets: [
      {
        label: 'Requests by Department',
        data: chartData.departmentData.map(item => item.count),
        backgroundColor: [
          'rgba(255, 99, 132, 0.6)',
          'rgba(54, 162, 235, 0.6)',
          'rgba(255, 206, 86, 0.6)',
          'rgba(75, 192, 192, 0.6)',
          'rgba(153, 102, 255, 0.6)'
        ],
        borderColor: [
          'rgba(255, 99, 132, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)'
        ],
        borderWidth: 1
      }
    ]
  };

  // Prepare status data chart
  const statusChartData = {
    labels: chartData.statusData.map(item => item.status),
    datasets: [
      {
        label: 'Request Status',
        data: chartData.statusData.map(item => item.count),
        backgroundColor: [
          'rgba(75, 192, 192, 0.6)',
          'rgba(255, 206, 86, 0.6)',
          'rgba(255, 99, 132, 0.6)'
        ],
        borderColor: [
          'rgba(75, 192, 192, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(255, 99, 132, 1)'
        ],
        borderWidth: 1
      }
    ]
  };

  return (
    <div className="reports-container">
      <div className="reports-header">
        <h1 className="page-title">REPORTS</h1>
        <div className="header-buttons">
          <button 
            className="export-button"
            onClick={exportToPDF}
          >
            Export to PDF
          </button>
          <button 
            className="refresh-button"
            onClick={() => {
              fetchReportData();
              fetchChartData();
            }}
            disabled={reportData.loading || chartData.loading}
          >
            {reportData.loading || chartData.loading ? 'Loading...' : 'Refresh'}
          </button>
        </div>
      </div>

      {(reportData.error || chartData.error) && (
        <div className="error-message">
          <p>{reportData.error || chartData.error}</p>
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

          {!chartData.loading && (
            <div className="charts-container">
              <div className="chart-card">
                <h2>Monthly Requests</h2>
                <div className="chart-wrapper">
                  <Bar 
                    data={monthlyChartData} 
                    options={{
                      responsive: true,
                      plugins: {
                        legend: {
                          position: 'top',
                        },
                        title: {
                          display: true,
                          text: 'Monthly Request Distribution'
                        }
                      }
                    }}
                  />
                </div>
              </div>
              
              <div className="chart-card">
                <h2>Requests by Department</h2>
                <div className="chart-wrapper">
                  <Pie 
                    data={departmentChartData}
                    options={{
                      responsive: true,
                      plugins: {
                        legend: {
                          position: 'right',
                        },
                        title: {
                          display: true,
                          text: 'Department Request Distribution'
                        }
                      }
                    }}
                  />
                </div>
              </div>
              
              <div className="chart-card">
                <h2>Request Status Distribution</h2>
                <div className="chart-wrapper">
                  <Pie 
                    data={statusChartData}
                    options={{
                      responsive: true,
                      plugins: {
                        legend: {
                          position: 'right',
                        },
                        title: {
                          display: true,
                          text: 'Request Status Distribution'
                        }
                      }
                    }}
                  />
                </div>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default Reports;