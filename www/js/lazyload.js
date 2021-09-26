const images = document.querySelectorAll("[data-src");

function preloadImage(img) {
    const src = img.getAttribute("data-src");
    console.log(src);
    if (!src) {
        return;
    }

    img.src = src;
    img.classList.add("lazyR");
}

const imgOptions = {
    threshold: 0
};

const imgObserver = new IntersectionObserver((entries, imgObserver) => {
    entries.forEach(entry => {
        if (!entry.isIntersecting) {
            return;
        } else {
            preloadImage(entry.target);
            imgObserver.unobserve(entry.target);
        }
    })
}, imgOptions);

images.forEach(image => {
    imgObserver.observe(image);
});

// BLOG

const blog = document.querySelectorAll(".articleBlog");

function preloadBlog(blog, x) {
    if (x & 1) {
        blog.classList.add("lazyDivL");
    } else {
        blog.classList.add("lazyDivR");
    }
}

const blogOptions = {
    threshold: 0
};

window.x = 1;

const blogObserver = new IntersectionObserver((entries, blogObserver) => {

    entries.forEach(entry => {

        if (!entry.isIntersecting) {
            return;
        } else {
            preloadBlog(entry.target, x);
            blogObserver.unobserve(entry.target);
        }
        window.x++;
        console.log(x);
    })
}, blogOptions);

blog.forEach(blog => {
    blogObserver.observe(blog);
});