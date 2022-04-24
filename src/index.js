import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';
import App from './App';
import reportWebVitals from './reportWebVitals';
import { loadDatabase } from './utils/Database';
import Spinner from './components/Spinner';

import 'jquery/dist/jquery.js';
import 'popper.js/dist/umd/popper.js';
import 'bootstrap/dist/js/bootstrap.js';
import 'jquery.scrollto/jquery.scrollTo.js';
import 'lightbox2/dist/js/lightbox';
import '@fortawesome/fontawesome-free/js/all.js';

import 'lightbox2/dist/css/lightbox.css';
import 'bootstrap/dist/css/bootstrap.css';
import './assets/css/theme.css';
import '@fortawesome/fontawesome-free/css/all.css';
import 'highlight.js/styles/atom-one-dark.css';
import 'react-toastify/dist/ReactToastify.css';

loadDatabase(`${process.env.PUBLIC_URL}/database.json`)
  .then(() =>
    // loading the application in case of success
    ReactDOM.render(
      <React.StrictMode>
        <App />
      </React.StrictMode>,
      document.getElementById('root')
    )
  )
  .catch(error => {
    // show error message if the database could not be loaded
    const setHtml = () => {return { __html : error }}
    ReactDOM.render(
      <React.StrictMode>
        <div>
          <p>Failed to load database!</p>
          <pre dangerouslySetInnerHTML={setHtml()}></pre>
        </div>
      </React.StrictMode>,
      document.getElementById('root')
    )}
  );

// loading database message
ReactDOM.render(
  <React.StrictMode>
    <div style={{ position: "absolute", top: 0, right: 0, bottom: 0, left: 0, textAlign: "center" }}>
      <Spinner />
      <div>Loading Database...</div>
    </div>
  </React.StrictMode>,
  document.getElementById('root')
)


// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
