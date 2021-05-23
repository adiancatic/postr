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

window.editPost = function editPost(e) {
    let postActions = $(e).parents(".post-actions");
    postActions.hide();

    const postBody = $(e).parents(".post").children(".body");
    let content = postBody.text();

    let ctaButtons = $("<div class='cta-buttons'></div>")
        .append($("<button class='btn btn-ghost btn-cancel' type='button'>Cancel</button>"))
        .append($("<button class='btn btn-primary btn-submit' type='submit'>Post</button>"))
    ;

    let textarea = $('<textarea name="content"></textarea>').val(content);
    let error = '<label class="error"><i class="fad fa-exclamation-triangle"></i>You can\'t submit a post containing only whitespace.</label>';
    let textareaWrapper = $('<div class="textarea-wrapper"></div>').append(textarea).append(error);
    let postId = $('<input type="hidden" name="postId">').val(postActions.parents(".post").data("id"));

    let form = $('<form method="post" name="post-update"></form>')
        .append(postId)
        .append(textareaWrapper)
        .append(ctaButtons)
    ;

    postBody
        .empty()
        .append(form)
    ;

    const btnCancel = $(".btn-cancel", postBody);
    const btnSubmit = $(".btn-submit", postBody);

    btnCancel.on("click", () => {
        postBody.empty().text(content);

        postActions.show();
    });

    btnSubmit.on("click", function(e) {
        e.preventDefault();

        let formHtml = postActions.parents(".post").find("form");
        const pattern = new RegExp("^\\s*$");

        if(pattern.test($("textarea", formHtml).val())) {
            form.addClass("js-error");
        } else {
            form.removeClass("js-error");

            $.ajax({
                url: "/post/update",
                type: "PUT",
                data: {"formData": formHtml.serialize()},
                success: (data) => {
                    content = $("textarea", postBody).val();
                    postBody.empty();
                    postBody.text(content);
                    postActions.show();
                }
            });
        }
    });
};

window.deletePost = function deletePost(e) {
    let post = $(e).parents(".post-item");

    $.ajax({
        url: "/post/delete",
        type: "DELETE",
        data: {"postId": $(".post", post).data("id")},
        success: () => {
            post.animate({"height": 0, "opacity": 0, "marginBottom": 0}, () => {
                post.remove();
            });
        }
    });
};