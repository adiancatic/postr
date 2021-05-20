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

    $.post("/post/create", {
        formData: form.serialize()
    }).done(() => {
        form.trigger("reset");
    });
};