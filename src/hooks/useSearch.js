import { useState } from 'react';
import { useHistory } from 'react-router-dom';

export default function useSearch(predefined = "") {
    const [term, setTerm] = useState(predefined);
    const history = useHistory();

    const handleSearch = event => {
        event.preventDefault();
        history.push({
            pathname: "/search",
            search: "?q=" + encodeURIComponent(term)
        });
    }

    return {
        searchTerm: term,
        setSearchTerm: setTerm,
        handleSearch: handleSearch
    }
}