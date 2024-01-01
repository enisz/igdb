import { Pipe, PipeTransform } from '@angular/core';
import { TokenService } from '../service/token.service';
import { CLIENT_ID_REGEXP, ACCESS_TOKEN_REGEXP } from '../app.constant';

@Pipe({
  name: 'token',
  standalone: true
})
export class TokenPipe implements PipeTransform {
  public constructor(
    private readonly tokenService: TokenService,
  ) {}

  transform(value: string, ...args: unknown[]): unknown {
    const { clientId, accessToken } = this.tokenService.getTokens();

    if (clientId && accessToken) {
      return value
        .replaceAll(CLIENT_ID_REGEXP, clientId)
        .replaceAll(ACCESS_TOKEN_REGEXP, accessToken);
    }

    return value;
  }
}
