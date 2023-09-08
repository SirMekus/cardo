/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

 import './bootstrap';
 import '../sass/app.scss'
 import '../css/app.css';
 import '@fortawesome/fontawesome-free/css/all.css'

import { createApp } from 'vue';

import { router, api } from "./router";
import { createPinia } from "pinia";
import { useStaffStore } from "./store/staffAuth.js";
import { useAuthStore } from "./store/auth.js";
import { useStudentStore } from "./store/studentAuth.js";
import { useGeneralAuthStore } from "./store/generalAuth.js";
import { useGuardianStore } from "./store/guardianAuth.js";
import { useAdminStore } from "./store/adminAuth.js";
import { showSpinner, removeSpinner } from "mmuo";
import { asyncComponent } from "./MyComponents.js"
import  vuelarPlugin  from "@sirmekus/vuelar"

import Login from './components/auth/Login.vue'
import SignUp from './components/auth/SignUp.vue'
import EmailVerificationNotice from './components/auth/EmailVerificationNotice.vue'
import ForgotPassword from './components/auth/ForgotPassword.vue'
import ResetPassword from './components/auth/ResetPassword.vue'
import StaffLogin from './components/auth/StaffLogin.vue'
import StudentLogin from './components/auth/StudentLogin.vue'
import GuardianLogin from './components/auth/GuardianLogin.vue'

let app = createApp({});

app.use(createPinia());

app.use(vuelarPlugin)

app.config.productionTip = false;

let component

for(component in asyncComponent){
    app.component(component, asyncComponent[component]);
}

app.component('Login', Login).component('SignUp', SignUp).component('VerifyEmail', EmailVerificationNotice).component('ForgotPassword', ForgotPassword).component('ResetPassword', ResetPassword).component('StaffLogin', StaffLogin).component('StudentLogin', StudentLogin).component('GuardianLogin', GuardianLogin);

router.beforeResolve(async (to, from) => {
    //This is specific to founder(s) of an organisation. Only founder(s) should have access/permission
    if (to.meta.requiresAuthentication) {
        const user = useAuthStore();
        if (!user.data || !sessionStorage.auth_checked) {
            try {
                const response = await axios.get(api.auth.auth_confirm);
                user.data = response.data;
            } catch (error) {
                //This is probably because user has not verified his or her email address yet. For now, for authenticated users, the major cause of 403 errors is when a user's email has not been verified.
                if(error.response.status === 403){
                    location.href = api.page.email_verify
                }
                else{
                    location.href = api.page.login
                }
            }
        }
        sessionStorage.auth_checked = true;
    }

    //This is specific to staff/workers of the organisation. Founder(s) can as well have access though
    if (to.meta.requiresAccess) {
        const staff = useStaffStore();
        const user = useAuthStore();
        if (!staff.data || !sessionStorage.staff_checked) {
            try {
                const response = await axios.get(api.auth.staff_accesss_point);
                if (response.data.type == "staff") {
                    staff.data = response.data.data;
                } else {
                    user.data = response.data.data;
                }
            } catch (error) {
                location.href = api.page.staff_login
            }
        }
        sessionStorage.staff_checked = true;
    }

    //This is specific to students. Only students should have access to their biodata for privacy. However, the biodata may be controlled from the dashboard of staffs.
    if (to.meta.requiresStudentAuth) {
        const student = useStudentStore();
        if (!student.data || !sessionStorage.student_checked) {
            try {
                const response = await axios.get(api.auth.student_accesss_point);
                student.data = response.data;
            } catch (error) {
                location.href = api.page.student_login
            }
        }
        sessionStorage.student_checked = true;
    }

    //Some routes are shared between all authenticated users. As long as a user has been authenticated in the past the user should have access to any of these routes even though the 'store' may be empty (this is possible on page reload or when browser shuts down).
    if (to.meta.requiresGeneralAccess) {
        const general = useGeneralAuthStore();
        if (!general.data || !sessionStorage.general_checked) {
            try {
                const response = await axios.get(api.auth.general_accesss_point);
                general.data = response.data;
            } catch (error) {
                location.href = api.page.login
            }
        }
        sessionStorage.general_checked = true;
    }

    //These routes are specially for guardians (or parents). No user can have access to any route defined here if not authenticated as a guardian.
    if (to.meta.requiresGuardianAccess) {
        const guardian = useGuardianStore();
        if (!guardian.data || !sessionStorage.guardian_checked) {
            try {
                const response = await axios.get(api.auth.guardian_accesss_point);
                guardian.data = response.data;
            } catch (error) {
                location.href = api.page.guardian_login
            }
        }
        sessionStorage.guardian_checked = true;
    }

    //These routes are specially for guardians (or parents). No user can have access to any route defined here if not authenticated as a guardian.
    if (to.meta.requiresAdminAccess) {
        const admin = useAdminStore();
        if (!admin.data || !sessionStorage.admin_checked) {
            try {
                const response = await axios.get(api.auth.admin_confirm);
                admin.data = response.data;
            } catch (error) {
                return { name: "admin_login" };
            }
        }
        sessionStorage.admin_checked = true;
    }

    if (to.meta.title) {
        document.title = to.meta.title;
    }

    return true;
});

router.beforeEach((to, from) => {
    if(from.name == undefined && (location.pathname != to.fullPath) && document.querySelector("body").children[0].nextElementSibling){
        // location.href = to.fullPath
        // return false
    }

    if(document.querySelector("#close-menu") && document.querySelector("#close-menu").parentElement.parentElement.classList.contains('show')){
        document.querySelector("#close-menu").click()
    }
    
    showSpinner();
});

router.afterEach((to, from) => {
    document.body.removeAttribute("style");
    removeSpinner();

    if(to.name == undefined && !document.querySelector("body").children[0].nextElementSibling){
        //location.reload()
    }
});

app.use(router);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
app.mount("#app");


let app2 = createApp({});
app2.component("VueFooter", asyncComponent.Footer);

if(document.querySelector("#spaFooter")){
    app2.use(router);
    app2.mount("#spaFooter");
}


export { app, app2 };
