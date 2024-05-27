import React from 'react';
import { Link } from 'react-router-dom';

function Navbar({ isAuthenticated, onLogout, username, avatar }) {
  return (
    <nav className="navbar">
      <Link to="/">Inicio</Link>
      <Link to="/about">Acerca de</Link>
      <Link to="/contact">Contacto</Link>
      {isAuthenticated ? (
        <>
          <span style={{ fontWeight: 'bold' }}>Hola, {username}</span>
          {avatar && <img src={`data:image/jpeg;base64,${avatar}`} alt="Avatar" />}
          <button onClick={onLogout}>Cerrar Sesión</button>
        </>
      ) : (
        <>
          <Link to="/login">Iniciar Sesión</Link>
          <Link to="/register">Registrar</Link>
        </>
      )}
    </nav>
  );
}

export default Navbar;