import React, { useState } from 'react';
import './addUser.css';

function AddUser() {
  const [formData, setFormData] = useState({
    firstname: '',
    middlename: '',
    lastname: '',
    department: '',
    email: '',
    username: '',
    password: '',
    confirmPassword: '',
    role: 'student'
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value
    });
    // Clear error when user starts typing again
    if (error) setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    // Validate form
    if (formData.password !== formData.confirmPassword) {
      setError("Passwords do not match!");
      setLoading(false);
      return;
    }

    if (formData.password.length < 6) {
      setError("Password must be at least 6 characters long");
      setLoading(false);
      return;
    }

    try {
      const response = await fetch("http://localhost/CampusReservationSystem/src/api/add_user.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();
      
      if (data.success) {
        setSuccess("User added successfully!");
        // Reset form
        setFormData({
          firstname: '',
          middlename: '',
          lastname: '',
          department: '',
          email: '',
          username: '',
          password: '',
          confirmPassword: '',
          role: 'student'
        });
      } else {
        setError(data.message || "Failed to add user. Please try again.");
      }
    } catch (error) {
      console.error("Error:", error);
      setError("Connection error. Please try again later.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="add-user-container">
      <h1 className="page-title">ADD USER</h1>
      
      {error && <div className="error-message">{error}</div>}
      {success && <div className="success-message">{success}</div>}
      
      <form onSubmit={handleSubmit} className="add-user-form">
        <div className="form-row">
          <div className="form-group">
            <label htmlFor="firstname">First Name:</label>
            <input 
              type="text" 
              id="firstname" 
              name="firstname" 
              value={formData.firstname} 
              onChange={handleChange} 
              required 
            />
          </div>

          <div className="form-group">
            <label htmlFor="middlename">Middle Name:</label>
            <input 
              type="text" 
              id="middlename" 
              name="middlename" 
              value={formData.middlename} 
              onChange={handleChange} 
            />
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="lastname">Last Name:</label>
            <input 
              type="text" 
              id="lastname" 
              name="lastname" 
              value={formData.lastname} 
              onChange={handleChange} 
              required 
            />
          </div>

          <div className="form-group">
            <label htmlFor="department">Department:</label>
            <select
              id="department"
              name="department"
              value={formData.department}
              onChange={handleChange}
              required
            >
              <option value="">Select Department</option>
              <option value="College of Computer Studies">College of Computer Studies</option>
              <option value="College of Accountancy">College of Accountancy</option>
              <option value="College of Arts And Science">College of Arts And Science</option>
              <option value="College Of Education">College Of Education</option>
              <option value="College of Hospitality Management and Tourism">College of Hospitality Management and Tourism</option>
              <option value="College Of Business Administration">College Of Business Administration</option>
              <option value="College of Health and Sciences">College of Health and Sciences</option>
              <option value="School of Psychology">School of Psychology</option>
              <option value="College of Maritime Education">College of Maritime Education</option>
              <option value="School of Mechanical Engineering">School of Mechanical Engineering</option>
            </select>
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="email">Email:</label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              value={formData.email} 
              onChange={handleChange} 
              required 
            />
          </div>

          <div className="form-group">
            <label htmlFor="username">Username:</label>
            <input 
              type="text" 
              id="username" 
              name="username" 
              value={formData.username} 
              onChange={handleChange} 
              required 
            />
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="password">Password:</label>
            <input 
              type="password" 
              id="password" 
              name="password" 
              value={formData.password} 
              onChange={handleChange} 
              required 
              minLength="6"
            />
          </div>

          <div className="form-group">
            <label htmlFor="confirmPassword">Confirm Password:</label>
            <input 
              type="password" 
              id="confirmPassword" 
              name="confirmPassword" 
              value={formData.confirmPassword} 
              onChange={handleChange} 
              required 
            />
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="role">Role:</label>
            <select
              id="role"
              name="role"
              value={formData.role}
              onChange={handleChange}
              required
            >
              <option value="student">Student</option>
              <option value="faculty">Faculty</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>

        <div className="button-container">
          <button 
            type="submit" 
            className="add-user-button" 
            disabled={loading}
          >
            {loading ? "ADDING USER..." : "ADD USER"}
          </button>
        </div>
      </form>
    </div>
  );
}

export default AddUser;