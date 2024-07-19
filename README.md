# 📷 th23 Gallery

Replace standard Wordpress Gallery block and legacy [gallery] shortcode handler, opening galleries using PhotoSwipe


## 🚀 Introduction

Wordpress has come a long way to be a fully featured content editor system, but the **default gallery does not keep up** with it. And while there are many plugins with various features, I did not find a small and simple one putting the **focus on enlarged images**.

[**PhotoSwipe**](https://photoswipe.com/) is a great basis and thus core component of my th23 Gallery plugin, which replaces the Wordpress default to output galleries into a way containing all key information required while maintaining a **simple and easy markup**.

> <img src="https://github.com/user-attachments/assets/23b3dd27-a28b-486f-91be-9535643bac87" alt="" height="200px">
> <img src="https://github.com/user-attachments/assets/7fed97bf-d11c-4479-8f4c-bf0fa2bf139c" alt="" height="200px">


`th23 Gallery` is built with some few goals in mind:

* Unintrusive way to **focus on images**
* Leveraging **PhotoSwipe / Lightbox-like** overlay
* Navigation between images in all galleries in a post via **intuitive swipe, keyboard or mouse** activity
* Compatibile with modern **blocks and legacy shortcodes**
* **Simple markup** and styling for thumbnail preview images (polaroid-style)
* **Plain styling** for medium / large preview images allowing for theme based style
* **Minimized footprint** on page loading


## ⚙️ Setup

Upload extracted files to your `wp-content/plugins` directory

No options required - **install, activate, enjoy** :-)

Image subtitle in lightbox is image **caption** (defined in gallery), image **description** (as in the media library) or image **title** (as in the media library) - in this order, whatever is given first

Only optional setting is the option to define a **default preview image size** for new galleries via the `Settings` / `Media` page in the admin panel

> [!NOTE]
> There is a [known inconsistency in core WP](https://core.trac.wordpress.org/ticket/40692) not showing this default correctly in new galleries - but it is taken correctly upon saving/including the gallery and upon further edits as well


## 🖐️ Usage

`th23 Gallery` works out-of-the-box and does not require any special handling - right after activation all embedded galleries will use PhotoSwipe to enlarge pictures.

> [!NOTE]
> Polaroid-style works only on galleries which have set the preview image size to `Thumbnail`

> [!NOTE]
> Previously inserted galleries might require a review to ensure link to media file and preview image size are set to work as expected - especially when also using the plugin on legacy `[gallery]` shortcodes.


## ❓ FAQ

Q: Can you provide **options** to change x, y or z behaviour in PhotoSwipe?

A: Yes, I could, but this plugin is supposed to be simple - in case you want many individual customization options, have a look at this [open source alternative by Arno Welzel](https://wordpress.org/plugins/lightbox-photoswipe/)


## 🤝 Contributors

Feel free to [raise issues](../../issues) or [contribute code](../../pulls) for improvements via GitHub.


## ©️ License

You are free to use this code in your projects as per the `GNU General Public License v3.0`. References to this repository are of course very welcome in return for my work 😉
