import { useEffect, useState } from 'react';

export default function useUserTokens() {
    const _clientId = localStorage.getItem("client_id") || sessionStorage.getItem("client_id");
    const _accessToken = localStorage.getItem("access_token") || sessionStorage.getItem("access_token");

    const [clientId, setClientId] = useState(_clientId == null ? "" : _clientId);
    const [accessToken, setAccessToken] = useState(_accessToken == null ? "" : _accessToken);
    const [storeTokens, setStoreTokens] = useState(localStorage.getItem("client_id") != null || localStorage.getItem("access_token") != null ? true : false);

    useEffect(() => {
        if(storeTokens) {
            if(clientId !== "") {
                sessionStorage.removeItem("client_id");
                localStorage.setItem("client_id", clientId);
            } else {
                sessionStorage.removeItem("client_id");
                localStorage.removeItem("client_id");
            }

            if(accessToken !== "") {
                sessionStorage.removeItem("access_token");
                localStorage.setItem("access_token", accessToken);
            } else {
                sessionStorage.removeItem("access_token");
                localStorage.removeItem("access_token");
            }
        } else {
            if(clientId !== "") {
                localStorage.removeItem("client_id");
                sessionStorage.setItem("client_id", clientId);
            } else {
                localStorage.removeItem("client_id");
                sessionStorage.removeItem("client_id");
            }

            if(accessToken !== "") {
                localStorage.removeItem("access_token");
                sessionStorage.setItem("access_token", accessToken);
            } else {
                localStorage.removeItem("access_token");
                sessionStorage.removeItem("access_token");
            }
        }
    }, [clientId, accessToken, storeTokens]);

    return {
        clientId: clientId,
        setClientId: setClientId,
        accessToken: accessToken,
        setAccessToken: setAccessToken,
        storeTokens: storeTokens,
        setStoreTokens: setStoreTokens
    };
}