function DeleteModal(did,dnam) {
    $('#md_tit_dnam').text(dnam);
    $('#md_imp_did').val(did);
    $('#ModalConfDel').modal();
}

function DetachModal(did,dnam) {
    $('#mdt_tit_dnam').text(dnam);
    $('#mdt_imp_did').val(did);
    $('#ModalConfDetach').modal();
}

function ModUserModal(uid,umel,unam,ugvn,usts) {
    $('#mum_imp_uid').val(uid);
    $('#mum_imp_mel').val(umel);
    $('#mum_imp_nam').val(unam);
    $('#mum_imp_gvn').val(ugvn);
    $('#mum_sel_sts').val(usts);
    $('#ModalUserModify').modal();
}

function DelUserModal(uid,unam) {
    $('#mud_tit_unam').text(unam);
    $('#mud_imp_uid').val(uid);
    $('#ModalUserDelete').modal();
}
