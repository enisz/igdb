import { Injectable } from '@angular/core';
import { TOKEN_VALIDATOR_PATTERN } from '../app.constant';
import { IToken } from '../interface/token.interface';

@Injectable({
  providedIn: 'root'
})
export class TokenService {

  private clientIdKey = 'clientId';
  private accessTokenKey = 'accessToken';

  public isRemembered(): boolean {
    return !!localStorage.getItem(this.clientIdKey);
  }

  public getTokens(): IToken {
    let clientId = localStorage.getItem(this.clientIdKey) || sessionStorage.getItem(this.clientIdKey) || '';
    let accessToken = localStorage.getItem(this.accessTokenKey) || sessionStorage.getItem(this.accessTokenKey) || '';

    const tokensAreValid = this.isTokenValid(clientId) && this.isTokenValid(accessToken);
    if (!tokensAreValid) {
      this.clearTokens();
      clientId = '';
      accessToken = '';
    }

    return { clientId, accessToken };
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

  public isTokenValid(token: string): boolean {
    return !!token.match(new RegExp(TOKEN_VALIDATOR_PATTERN));
  }
}
