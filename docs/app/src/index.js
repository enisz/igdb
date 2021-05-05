import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';
import reportWebVitals from './reportWebVitals';

import 'jquery/dist/jquery.js';
import 'popper.js/dist/umd/popper.js';
import 'bootstrap/dist/js/bootstrap.js';
import 'jquery.scrollto/jquery.scrollTo.js';
import 'stickyfilljs/dist/stickyfill.js';
import 'ekko-lightbox/dist/ekko-lightbox.js';
import '@fortawesome/fontawesome-free/js/all.js';

import 'ekko-lightbox/dist/ekko-lightbox.css';
import 'bootstrap/dist/css/bootstrap.css';
import './assets/css/styles.css';
import '@fortawesome/fontawesome-free/css/all.css';
import 'eleganticons/css/style.css';
import 'highlight.js/styles/atom-one-dark.css';

ReactDOM.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
  document.getElementById('root')
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
