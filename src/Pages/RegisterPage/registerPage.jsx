import React, { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import "./registerPage.css";

function RegisterPage() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    firstName: "",
    middleName: "",
    lastName: "",
    department: "",
    email: "",
    username: "",
    password: "",
    confirmPassword: "",
  });
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    // Clear error when user starts typing again
    if (error) setError("");
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
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
      const response = await fetch("http://localhost/CampusReservationSystem-main/CampusReservationSystem-main/src/Pages/RegisterPage/signup.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      });
  
      const data = await response.json();
      console.log("Server Response:", data);
      
      if (data.success) {
        // Registration successful
        alert("Registration successful! You can now log in.");
        navigate("/"); // Redirect to login page
      } else {
        // Registration failed
        setError(data.message || "Registration failed. Please try again.");
      }
    } catch (error) {
      console.error("Error:", error);
      setError("Connection error. Please try again later.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="register-container">
      <h1>SIGN UP</h1>
      
      {error && <div className="error-message">{error}</div>}
      
      <form onSubmit={handleSubmit} className="register-form">
        <div className="left-column">
          <label htmlFor="firstName">First Name:</label>
          <input type="text" id="firstName" name="firstName" value={formData.firstName} onChange={handleChange} required />

          <label htmlFor="middleName">Middle Name:</label>
          <input type="text" id="middleName" name="middleName" value={formData.middleName} onChange={handleChange} />

          <label htmlFor="lastName">Last Name:</label>
          <input type="text" id="lastName" name="lastName" value={formData.lastName} onChange={handleChange} required />

          <label htmlFor="department">Department:</label>
          <input type="text" id="department" name="department" value={formData.department} onChange={handleChange} required />

          <label htmlFor="email">Email:</label>
          <input type="email" id="email" name="email" value={formData.email} onChange={handleChange} required />
        </div>

        <div className="right-column">
          <label htmlFor="username">Username:</label>
          <input type="text" id="username" name="username" value={formData.username} onChange={handleChange} required />

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

          <label htmlFor="confirmPassword">Confirm Password:</label>
          <input 
            type="password" 
            id="confirmPassword" 
            name="confirmPassword" 
            value={formData.confirmPassword} 
            onChange={handleChange} 
            required 
          />
          
          <button 
            type="submit" 
            className="signup-button" 
            disabled={loading}
          >
            {loading ? "SIGNING UP..." : "SIGN UP"}
          </button>
        </div>
      </form>

      <p>Already have an account? <Link to="/">Login here</Link></p>
    </div>
  );
}

export default RegisterPage;
