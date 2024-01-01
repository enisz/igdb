import { Injectable } from '@angular/core';
import { IToken } from '../interface/token.interface';

@Injectable({
  providedIn: 'root'
})
export class TokenService {

  constructor() { }

  public getTokens(): IToken {
    return {
      clientId: localStorage.getItem('client-id') || sessionStorage.getItem('client-id') || '',
      accessToken: localStorage.getItem('access-token') || sessionStorage.getItem('access-token') || '',
    };
  }

  public setTokens(token: IToken, remember: boolean): void {
    const { clientId, accessToken } = token;

    if (remember) {
      localStorage.setItem('client-id', clientId);
      localStorage.setItem('access-token', accessToken);
    } else {
      sessionStorage.setItem('client-id', clientId);
      sessionStorage.setItem('access-token', accessToken);
    }
  }

  public clearTokens(): void {
    localStorage.removeItem('client-id');
    localStorage.removeItem('access-token');
    sessionStorage.removeItem('client-id');
    sessionStorage.removeItem('access-token');
  }
}
