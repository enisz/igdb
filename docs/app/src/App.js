import { BrowserRouter, Redirect, Route, Switch } from 'react-router-dom';
import HomePage from './pages/HomePage';
import DocumentPage from './pages/DocumentPage';

import jQuery from 'jquery';
window.jQuery = jQuery;

function App() {
  return (
    <>
        <div className="page-wrapper">
            <BrowserRouter basename="/igdb">
                <Switch>
                    <Redirect from="/" to="/home" exact />
                    <Route path="/home" component={HomePage} exact />
                    <Route path="/document/:document" component={DocumentPage} exact />
                </Switch>
            </BrowserRouter>
        </div>

        <footer className="footer text-center">
            <div className="container">
                <small className="copyright">Designed with <i className="fas fa-heart"></i> by <a href="https://themes.3rdwavemedia.com/" target="_blank" rel="noreferrer">Xiaoying Riley</a> for developers</small>
            </div>
        </footer>
    </>
  );
}

export default App;
