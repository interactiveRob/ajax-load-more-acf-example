export default class LoadMore {
    constructor() {
        this.$node = $('[data-js="load-more"]');
        this.activeClass = 'is-active';
        this.args = {};
        this.loading = false;
    }

    setArgs($target) {
        let query = $target.attr('data-query');
        let page = $target.attr('data-page');

        query = JSON.parse(query);
        query.paged = page;

        this.args = {
            $target,
            text: $target.text(),
            query,
            page: $target.attr('data-page'),
            maxpage: $target.attr('data-maxpage'),
        };

        return this.args;
    }

    setLoadMoreBtnText(text) {
        this.args.$target.find('a').text(text);
    }

    updateLoadMoreBtnData() {
        let newQuery = this.args.query;

        this.$node.attr({
            'data-query': JSON.stringify(newQuery),
            'data-page': this.args.page,
            'data-maxpage': this.args.maxpage,
        });

        this.$node.trigger('refresh');
    }

    render(data) {
        let $appendTarget = $(this.args.query.append_target);

        //get baseline height for animation
        let targetHeight = $appendTarget[0].clientHeight;
        $appendTarget.css('max-height', 'unset');

        $appendTarget.append(data);
        
        let fullHeight = $appendTarget[0].clientHeight;
        console.log('fullHeight is ' + fullHeight);

        //animate to full height with new content
        $appendTarget.css('max-height', targetHeight);

        setTimeout(() => {
            $appendTarget.css('max-height', fullHeight);
        }, 25);

        if (this.args.maxpage <= 1 || this.args.page >= this.args.maxpage) {
            this.$node.hide();
        }
    }

    onAjaxSuccess(data) {
        this.setLoadMoreBtnText(this.args.text);
        this.$node.show();

        if (!data) {
            this.$node.hide();

            this.loading = false;
            return;
        }

        let newPage = parseInt(this.args.page) + 1;

        this.args.query.paged = newPage;
        this.args.page = newPage;

        let newQuery = JSON.stringify(this.args.query);

        this.args.$target.attr({
            'data-page': newPage,
            'data-query': newQuery,
        });

        this.loading = false;

        this.render(data);
    }

    initAjax() {
        this.loading = true;

        let data = {
            action: 'load_more_posts',
            query: JSON.stringify(this.args.query),
            page: this.args.query.paged,
        };

        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data,
            error: (error) => console.log(error),
            beforeSend: this.setLoadMoreBtnText.bind(this, 'Loading...'),
            success: this.onAjaxSuccess.bind(this),
        });
    }

    onBtnClick(event) {
        event.preventDefault();

        if (this.loading) return;

        let $currentTarget = $(event.currentTarget);
        this.setArgs($currentTarget);

        this.initAjax();
    }

    setEventBindings() {
        this.$node.on('click', this.onBtnClick.bind(this));
    }

    init() {
        this.setEventBindings();
    }
}