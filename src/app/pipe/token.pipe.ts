import { Pipe, PipeTransform } from '@angular/core';
import { ACCESS_TOKEN_REGEXP, CLIENT_ID_REGEXP } from '../app.constant';
import { TokenService } from '../service/token.service';

@Pipe({
  name: 'token',
  standalone: true
})
export class TokenPipe implements PipeTransform {
  public constructor(
    private readonly tokenService: TokenService,
  ) {}

  transform(value: string, ...args: unknown[]): string {
    const { clientId, accessToken } = this.tokenService.getTokens();

    if (clientId && accessToken) {
      return value
        .replaceAll(CLIENT_ID_REGEXP, clientId)
        .replaceAll(ACCESS_TOKEN_REGEXP, accessToken);
    }

    return value;
  }
}
