import { Injectable } from '@angular/core';
import { IToken } from '../interface/token.interface';

@Injectable({
  providedIn: 'root'
})
export class TokenService {

  private clientIdKey = 'clientId';
  private accessTokenKey = 'accessToken';

  constructor() { }

  public isRemembered(): boolean {
    return !!localStorage.getItem(this.clientIdKey);
  }

  public getTokens(): IToken {
    return {
      clientId: localStorage.getItem(this.clientIdKey) || sessionStorage.getItem(this.clientIdKey) || '',
      accessToken: localStorage.getItem(this.accessTokenKey) || sessionStorage.getItem(this.accessTokenKey) || '',
    };
  }

  public setTokens(clientId: string, accessToken: string, remember: boolean): void {
    if (remember) {
      localStorage.setItem(this.clientIdKey, clientId);
      localStorage.setItem(this.accessTokenKey, accessToken);
    } else {
      sessionStorage.setItem(this.clientIdKey, clientId);
      sessionStorage.setItem(this.accessTokenKey, accessToken);
    }
  }

  public clearTokens(): void {
    localStorage.removeItem(this.clientIdKey);
    localStorage.removeItem(this.accessTokenKey);
    sessionStorage.removeItem(this.clientIdKey);
    sessionStorage.removeItem(this.accessTokenKey);
  }
}
