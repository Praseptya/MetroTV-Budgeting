(function () {
    const $ = (s, c = document) => c.querySelector(s);
    const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

    /* ---- Sections & forms ---- */
    const editWrap = $("#editCardWrap");
    const resetWrap = $("#resetCardWrap");
    const reassignWrap = $("#reassignCardWrap");

    const formEdit = $("#formEdit");
    const formReset = $("#formReset");
    const formReassign = $("#formReassign");

    /* ---- Helpers ---- */
    const show = (el) => {
        if (el) el.style.display = "";
    };
    const hide = (el) => {
        if (el) el.style.display = "none";
    };
    const closeAll = () => {
        hide(editWrap);
        hide(resetWrap);
        hide(reassignWrap);
    };

    // Replace trailing "/0" or ending "0" in route template
    function buildAction(tpl, id) {
        if (!tpl) return "";
        // ganti .../0 atau ...0 di akhir string dengan /<id>
        return tpl.replace(/0(?=$|\/?$)/, String(id));
    }

    /* =========================================================
     *  EDIT USER
     * ======================================================= */
    $$(".btn-edit").forEach((btn) => {
        btn.addEventListener("click", () => {
            const tr = btn.closest("tr");
            if (!tr) return;

            const id = tr.dataset.id;
            const name = tr.dataset.name || "";
            const email = tr.dataset.email || "";
            const uname = tr.dataset.username || "";
            const role = tr.dataset.role_id || "";

            // isi field
            $("#edit_name").value = name;
            $("#edit_email").value = email;
            $("#edit_username").value = uname;
            $("#edit_role").value = role;

            // set action
            const tpl = formEdit?.dataset.updateTpl || "";
            formEdit.action = buildAction(tpl, id);

            closeAll();
            show(editWrap);
            window.scrollTo({
                top: editWrap.offsetTop - 12,
                behavior: "smooth",
            });
        });
    });

    $("#btnCloseEdit")?.addEventListener("click", () => {
        formEdit?.reset();
        hide(editWrap);
    });

    /* =========================================================
     *  RESET PASSWORD
     * ======================================================= */
    $$(".btn-reset").forEach((btn) => {
        btn.addEventListener("click", () => {
            const tr = btn.closest("tr");
            if (!tr) return;

            const id = tr.dataset.id;
            const name = tr.dataset.name || "";

            // bersihkan field
            $("#reset_pw1").value = "";
            $("#reset_pw2").value = "";

            // set action
            const tpl = formReset?.dataset.resetTpl || "";
            formReset.action = buildAction(tpl, id);

            closeAll();
            show(resetWrap);
            window.scrollTo({
                top: resetWrap.offsetTop - 12,
                behavior: "smooth",
            });
        });
    });

    $("#btnCloseReset")?.addEventListener("click", () => {
        formReset?.reset();
        hide(resetWrap);
    });

    // Validasi konfirmasi password
    formReset?.addEventListener("submit", (e) => {
        const p1 = $("#reset_pw1").value.trim();
        const p2 = $("#reset_pw2").value.trim();
        if (!p1 || !p2 || p1 !== p2) {
            e.preventDefault();
            alert("Password & konfirmasi harus sama.");
        }
    });

    /* =========================================================
     *  REASSIGN & DELETE
     * ======================================================= */
    $$(".btn-reassign").forEach((btn) => {
        btn.addEventListener("click", () => {
            const tr = btn.closest("tr");
            if (!tr) return;

            const id = tr.dataset.id;

            // reset select
            const sel = $("#target_user_id");
            if (sel) sel.value = "";

            // set action
            const tpl = formReassign?.dataset.reassignTpl || "";
            formReassign.action = buildAction(tpl, id);

            closeAll();
            show(reassignWrap);
            window.scrollTo({
                top: reassignWrap.offsetTop - 12,
                behavior: "smooth",
            });
        });
    });

    $("#btnCloseReassign")?.addEventListener("click", () => {
        formReassign?.reset();
        hide(reassignWrap);
    });

    /* =========================================================
     *  Search filter client-side (opsional)
     * ======================================================= */
    $("#searchInput")?.addEventListener("input", function () {
        const q = this.value.toLowerCase();
        $$("#userTable tbody tr").forEach((tr) => {
            tr.style.display = tr.textContent.toLowerCase().includes(q)
                ? ""
                : "none";
        });
    });
})();
