import {
    mdiMenu,
    mdiClockOutline,
    mdiCloud,
    mdiCrop,
    mdiAccount,
    mdiCogOutline,
    mdiEmail,
    mdiLogout,
    mdiThemeLightDark,
    mdiGithub,
    mdiReact,
} from "@mdi/js";

export default [
    {
        icon: mdiMenu,
        label: "Update  ",
        menu: [
            {
                icon: mdiClockOutline,
                label: "Item One",
            },
            {
                icon: mdiCloud,
                label: "Item Two",
            },
            {
                isDivider: true,
            },
            {
                icon: mdiCrop,
                label: "Item Last",
            },
        ],
    },
    {
        isCurrentUser: true,
        menu: [
            {
                icon: mdiAccount,
                label: "My Profile",
                to: "/profile",
            },
            {
                icon: mdiCogOutline,
                label: "Settings",
            },
            {
                icon: mdiEmail,
                label: "Messages",
            },
            {
                isDivider: true,
            },
            {
                icon: mdiLogout,
                label: "Log Out",
                isLogout: true,
            },
        ],
    },
    {
        icon: mdiThemeLightDark,
        label: "Light/Dark",
        isDesktopNoLabel: true,
    },
    {
        icon: mdiLogout,
        label: "Log out",
        isDesktopNoLabel: true,
        isLogout: true,
    },
];
