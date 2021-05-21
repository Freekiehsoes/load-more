class WpLoadMore {
    constructor(postType, postsPerPage, loadMoreSelector, outletSelector) {
        this.postType = postType;
        this.postsPerPage = postsPerPage;
        this.page = 1;

        this.loadMoreSelector = loadMoreSelector;
        this.outletSelector = outletSelector;

        document.addEventListener("DOMContentLoaded", () => this.init());
    }

    init() {
        this.loadMoreElement = document.querySelectorAll(this.loadMoreSelector);
        this.outletElement = document.querySelector(this.outletSelector);

        this.initWatchers();

        this.loadMore();
    }

    loadMore() {
        this.makeRestRequest().then(data => {
            this.success(data)
        });
    }

    initWatchers() {
        for (let element of this.loadMoreElement) {
            element.addEventListener('click', e => {
                e.preventDefault();
                this.loadMore();
            });
        }
    }

    async makeRestRequest() {
        const url = new URL(document.location);
        const params = {
            postType: this.postType,
            postsPerPage: this.postsPerPage,
            pageNr: this.page
        };
        url.search = new URLSearchParams(params).toString();

        return fetch(url).then(d => d.json());
    }

    success(data) {
        const {html, totalCount, curCount} = data;

        const doc = new DOMParser().parseFromString(html, 'text/html');
        const _html = doc.documentElement.textContent;

        this.outletElement.innerHTML += _html;

        if (curCount >= totalCount) {
            for (let element of this.loadMoreElement) {
                const parent = element.parentNode;
                parent.removeChild(element);
            }
        }

        this.page += 1;
    }
}

new WpLoadMore(
    WP_LOAD_MORE_POST_TYPE,
    WP_LOAD_MORE_POSTS_PER_PAGE,
    WP_LOAD_MORE_SELECTOR,
    WP_LOAD_MORE_OUTLET_SELECTOR
);