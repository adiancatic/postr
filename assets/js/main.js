$(document).ready(() => {



});

window.followHandler = function followHandler(id) {
    $.post("/u/follow", {
        id: id
    }).done((data) => {
        const parsedData = JSON.parse(data);

        if(parsedData.actionType === "follow") {
            $(".btn-follow").replaceWith(parsedData.followBtn);
        } else if (parsedData.actionType === "unfollow") {
            $(".btn-unfollow").replaceWith(parsedData.followBtn);
        }

        $(".follow-stats").replaceWith(parsedData.followStats);
    });
};

window.createPost = function createPost(e) {
    e.preventDefault();

    const form = $(".post-create form");

    let textarea = $("textarea", form);
    const pattern = new RegExp("^\\s*$");

    if(pattern.test(textarea.val())) {
        form.addClass("js-error");
        return;
    }

    form.removeClass("js-error");

    $.post("/post/create", {
        formData: form.serialize()
    }).done((data) => {
        form.trigger("reset");
        updatePostListWithLastPost(data);
    });
};

function updatePostListWithLastPost(postHtml) {
    $(".post-list").prepend(postHtml);

    let post = $(".post-list article:first-child");
    const postHeight = post.outerHeight(true);

    post
        .wrap('<div class="post-wrapper"></div>')
        .parent()
        .css({"height": 0, "opacity": 0})
        .animate({"height": postHeight, "opacity": 1}, function () {
            $(this).children(":first-child").unwrap()
        });
}