export interface ICommits {
    url: string;
    sha: string;
    node_id: string;
    html_url: string;
    comments_url: string;
    commit: {
        url: string;
        author: {
            name: string;
            email: string;
            date: string;
        };
        committer: {
            name: string;
            email: string;
            date: string;
        };
        message: string;
        comment_count: number;
        tree: {
            sha: string;
            url: string;
        };
        verification: {
            verified: boolean;
            reason: string;
            payload: string | null;
            signature: string | null;
        };
    };
    author: IPerson;
    committer: IPerson;
    parents: {
        sha: string;
        url: string;
        html_url?: string;
    }[];
    stats: {
        additions: number;
        deletions: number;
        total: number;
    };
    files: {
        sha: string;
        filename: string;
        status: 'added' | 'removed' | 'modified' | 'renamed' | 'copied' | 'changed' | 'unchanged';
        additions: number;
        deletions: number;
        changes: number;
        blob_url: string;
        raw_url: string;
        contents_url: string;
        patch: string;
        previous_filename: string;
    }[];
}

export interface IPerson {
    name: string | null;
    email: string | null;
    login: string;
    id: number;
    node_id: string;
    avatar_url: string;
    gravatar_id: string | null;
    url: string;
    html_url: string;
    followers_url: string;
    following_url: string;
    gists_url: string;
    starred_url: string;
    subscriptions_url: string;
    organizations_url: string;
    repos_url: string;
    events_url: string;
    received_events_url: string;
    type: string;
    site_admin: boolean;
    starred_at: string;
}

export interface IRelease {
    url: string;
    html_url: string;
    assets_url: string;
    upload_url: string;
    tarball_url: string | null;
    zipball_url: string | null;
    id: number;
    node_id: string;
    tag_name: string;
    target_commitish: string;
    name: string | null;
    body: string | null;
    draft: boolean;
    prerelease: boolean;
    created_at: string;
    published_at: string | null;
    author: IPerson,
    assets: {
        url: string;
        browser_download_url: string;
        id: number;
        node_id: string;
        name: string;
        label: string | null;
        state: 'uploaded' | 'open';
        content_type: string;
        size: number;
        download_count: number;
        created_at: string;
        updated_at: string;
        uploader: IPerson,
    }[];
    body_html: string;
    body_text: string;
    mentions_count: number;
    discussion_url: string;
    reactions: {
        url: string;
        total_count: number;
        '+1': number;
        '-1': number;
        laugh: number;
        confused: number;
        heart: number;
        hooray: number;
        eyes: number;
        rocket: number;
    }
}
