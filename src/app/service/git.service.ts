import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { firstValueFrom, Observable } from 'rxjs';
import { ICommits, IRelease } from '../interface/git.interface';

@Injectable({
  providedIn: 'root'
})
export class GitService {
  private api: URL = new URL('https://api.github.com/repos/enisz/igdb/');

  constructor(
    private readonly httpClient: HttpClient,
  ) {}

  public async getLatestCommits(count: number): Promise<ICommits[]> {
    const url = new URL('commits', this.api);
    url.searchParams.append('per_page', count.toString());

    return firstValueFrom(this.httpClient.get<ICommits[]>(url.href));
  }

  public async getLatestRelease(): Promise<IRelease> {
    const url = new URL('releases/latest', this.api);
    return firstValueFrom(this.httpClient.get<any>(url.href));
  }
}
