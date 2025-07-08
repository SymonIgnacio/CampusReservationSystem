import React, { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { createUserWithEmailAndPassword, sendEmailVerification } from "firebase/auth";
import { auth } from "../../firebase";
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
      // First check if email exists in local database
      const checkResponse = await fetch("http://localhost/CampusReservationSystem/src/api/check_email.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email: formData.email }),
      });
      
      const checkData = await checkResponse.json();
      if (checkData.exists) {
        setError("Email is already registered. Please use a different email or login.");
        setLoading(false);
        return;
      }
      
      // Create Firebase user
      const userCredential = await createUserWithEmailAndPassword(auth, formData.email, formData.password);
      const user = userCredential.user;
      
      // Send email verification
      await sendEmailVerification(user);
      
      // Save user data to your database
      try {
        const response = await fetch("http://localhost/CampusReservationSystem/src/api/firebase_register.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            username: formData.username,
            email: formData.email,
            firstName: formData.firstName,
            lastName: formData.lastName,
            department: formData.department,
            firebaseUid: user.uid
          }),
        });
    
        const data = await response.json();
        
        if (data.success) {
          alert("Registration successful! Please check your email to verify your account before logging in.");
          navigate("/");
        } else {
          setError(data.message || "Registration failed. Please try again.");
        }
      } catch (fetchError) {
        console.error("Database save failed:", fetchError);
        setError("Registration failed to save user data. Please try again.");
        setLoading(false);
        return;
      }
    } catch (error) {
      console.error("Error:", error);
      if (error.code === 'auth/email-already-in-use') {
        // Check if user just needs to verify email
        setError("Email is already registered. If you haven't verified your email yet, please check your inbox.");
      } else if (error.code === 'auth/weak-password') {
        setError("Password is too weak");
      } else {
        setError(error.message || "Registration failed. Please try again.");
      }
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
          <select id="department" name="department" value={formData.department} onChange={handleChange} required>
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
