import './bootstrap.js';
import { registerVueControllerComponents } from '@symfony/ux-vue';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

import './styles/paginator.css';
import './styles/common.css';
import './styles/modal.css';
import './styles/dropdown.scss';
import './styles/component/checkbox.scss';
import './styles/health.scss';
import './styles/loader.scss';
import './styles/listInfoGrid.scss';
import './styles/bootstrap.scss';
import './styles/ck-content-styles.scss';
import './styles/notification.scss';
import './styles/reveal.css';
import './styles/carrousel.css';
import './styles/verticalStepProgress.css';

import './js/app.js';
import './js/navigation.js';
import './js/carrousel';
import './js/input-file';
import './js/modal';
import './js/reveal.js';
import './js/js-datepicker.js';
import './js/form.js';
import './js/slideshow.js';
import './js/notification.js';
registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/));