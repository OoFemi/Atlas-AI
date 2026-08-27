console.log("dashboard.js loaded");

/* -----------------------------
   Navigation
----------------------------- */

function showSection(sectionId) {

    document.querySelectorAll(".section").forEach(section => {
        section.classList.remove("active");
    });

    document.querySelectorAll(".sidebar-menu button").forEach(button => {
        button.classList.remove("active");
    });

    const section =
        document.getElementById(sectionId);

    if (section) {
        section.classList.add("active");
    }
}

/* -----------------------------
   Load Users
----------------------------- */

async function loadUsers() {

    try {

        const response =
            await fetch("get_users.php");

        const users =
            await response.json();

        console.log("Users:", users);

        const table =
            document.getElementById("userTable");

        if (!table) {
            return;
        }

        table.innerHTML = "";

        const count =
            document.getElementById("userCount");

        if (count) {
            count.innerText =
                `Showing ${users.length} active identities`;
        }

        const dashboardUsers =
            document.getElementById("dashboardUsers");

        if (dashboardUsers) {
            dashboardUsers.innerText =
                users.length;
        }

        const dashboardAdmins =
            document.getElementById("dashboardAdmins");

        if (dashboardAdmins) {
            dashboardAdmins.innerText =
                users.filter(
                    user => user.role === "admin"
                ).length;
        }

        users.forEach(user => {

    const row = `
        <tr>
            <td>${user.id || ""}</td>
            <td>${user.user_id || ""}</td>
            <td>${user.first_name || ""}</td>
            <td>${user.last_name || ""}</td>
            <td>${user.email || ""}</td>
            <td>${user.username || ""}</td>
            <td>${user.role || ""}</td>
            <td>${user.group_id || ""}</td>
            <td>${user.department || ""}</td>
            <td>${user.status || ""}</td>
            <td>
                <button onclick="editUser(${user.id})">
                    Edit
                </button>

                <button onclick="deleteUser(${user.id})">
                    Delete
                </button>
            </td>
        </tr>
    `;

    table.insertAdjacentHTML(
        "beforeend",
        row
    );

});

    }
    catch(error) {

        console.error(
            "Failed loading users:",
            error
        );

    }

}

/* -----------------------------
   Departments
----------------------------- */

async function loadDepartments() {

    try {

        const response =
            await fetch(
                "get_departments.php"
            );

        const departments =
            await response.json();

        console.log(
            "Departments:",
            departments
        );

        const dropdown =
            document.getElementById(
                "department"
            );

        if (!dropdown) {
            return;
        }

        dropdown.innerHTML = "";

        departments.forEach(dept => {

            dropdown.innerHTML += `
                <option value="${dept.group_id}">
                    ${dept.department_name}
                </option>
            `;

        });

    }
    catch(error) {

        console.error(
            "Department load failed:",
            error
        );

    }

}

async function loadDepartmentFilter() {

    try {

        const response =
            await fetch(
                "get_departments.php"
            );

        const departments =
            await response.json();

        const filter =
            document.getElementById(
                "departmentFilter"
            );

        if (!filter) {
            return;
        }

        departments.forEach(dept => {

            filter.innerHTML += `
                <option value="${dept.department_name}">
                    ${dept.department_name}
                </option>
            `;

        });

    }
    catch(error) {

        console.error(
            "Department filter failed:",
            error
        );

    }

}

/* -----------------------------
   Create User
----------------------------- */

async function createUser() {

    try {

        const deptDropdown =
            document.getElementById("department");

        const departmentName =
            deptDropdown.options[
                deptDropdown.selectedIndex
            ].text;

        const response =
            await fetch(
                "create_user.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type":
                            "application/json"
                    },
                    body: JSON.stringify({
                        first_name:
                            document.getElementById("firstName").value,

                        last_name:
                            document.getElementById("lastName").value,

                        email:
                            document.getElementById("email").value,

                        username:
                            document.getElementById("newUsername").value,

                        password:
                            document.getElementById("newPassword").value,

                        role:
                            document.getElementById("newRole").value,

                        group_id:
                            deptDropdown.value,

                        department:
                            departmentName
                    })
                }
            );

        const result =
            await response.json();

        if (result.success) {

            alert("User Created");

            loadUsers();

        } else {

            alert(
                result.message ||
                "Failed to create user"
            );

        }

    }
    catch(error) {

        console.error(error);

        alert(
            "Error creating user"
        );

    }

}

/* -----------------------------
   Edit User
----------------------------- */

function editUser(id) {

    alert(
        "Edit User ID: " + id
    );

}

/* -----------------------------
   Delete User
----------------------------- */

async function deleteUser(id) {

    if (!confirm(
        "Delete this user?"
    )) {
        return;
    }

    try {

        const response =
            await fetch(
                "delete_user.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type":
                            "application/json"
                    },
                    body: JSON.stringify({
                        id: id
                    })
                }
            );

        const result =
            await response.json();

        if (result.success) {

            alert(
                "User Deleted"
            );

            loadUsers();

        } else {

            alert(
                result.message
            );

        }

    }
    catch(error) {

        console.error(error);

        alert(
            "Delete Failed"
        );

    }

}

/* -----------------------------
   User Search
----------------------------- */

function initialiseUserSearch() {

    const search =
        document.getElementById(
            "searchUser"
        );

    if (!search) {
        return;
    }

    search.addEventListener(
        "keyup",
        function() {

            const value =
                this.value.toLowerCase();

            document
                .querySelectorAll(
                    "#userTable tr"
                )
                .forEach(row => {

                    row.style.display =
                        row.innerText
                            .toLowerCase()
                            .includes(value)
                            ? ""
                            : "none";

                });

        }
    );

}

/* -----------------------------
   Startup
----------------------------- */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        console.log(
            "DOM loaded"
        );

        loadUsers();
        loadDepartments();
        loadDepartmentFilter();
        initialiseUserSearch();

    }
);
