import React, { useState } from 'react';
import axios from 'axios';

function Login({ onLogin }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState(null);

  const handleSubmit = (e) => {
    e.preventDefault();
    axios.post('http://localhost:5000/api/login', { email, password })
    .then(response => {
      localStorage.setItem('token', response.data.token);
      onLogin();
      // Redirige al usuario a la página de inicio
      window.location.href = '/'; // Asegúrate de cambiar esto a la ruta correcta
    })
    .catch(error => {
      console.error('Login failed:', error);
      // Muestra un mensaje de error al usuario
      setError('Correo electrónico o contraseña incorrectos');
    });  
  };

  return (
    <div className="login-form">
      <h2>Iniciar Sesión</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label>Email:</label>
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </div>
        <div>
            <label>Contraseña:</label>
            <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
            />
            {error && <div style={{ color: 'red' }}>{error}</div>}
            <button type="submit">Iniciar Sesión</button>
        </div>
      </form>
    </div>
  );
}

export default Login;