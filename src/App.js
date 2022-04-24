import jQuery from 'jquery';
import { BrowserRouter, Redirect, Route, Switch } from 'react-router-dom';
import HomePage from './pages/HomePage';
import DocumentationPage from './pages/DocumentationPage';
import SearchPage from './pages/SearchPage';
import { ToastContextProvider } from './contexts/ToastContext';

window.jQuery = jQuery;

export default function App() {
  return (
    <ToastContextProvider>
      <BrowserRouter basename="/igdb">
        <Switch>
          <Redirect from="/" to="/home" exact />
          <Route path="/home" component={HomePage} exact />
          <Route path="/documentation" component={DocumentationPage} exact />
          <Route path="/search" component={SearchPage} exact />
        </Switch>
      </BrowserRouter>
    </ToastContextProvider>
  );
}