import jQuery from 'jquery';
import { HashRouter, Redirect, Route, Switch } from 'react-router-dom';
import HomePage from './pages/HomePage';
import DocumentationPage from './pages/DocumentationPage';
import SearchPage from './pages/SearchPage';

window.jQuery = jQuery;

export default function App() {
  return (
    <HashRouter basename="/igdb">
      <Switch>
        <Redirect from="/" to="/home" exact />
        <Route path="/home" component={HomePage} />
        <Route path="/documentation" component={DocumentationPage} exact />
        <Route path="/search" component={SearchPage} exact />
      </Switch>
    </HashRouter>
  );
}