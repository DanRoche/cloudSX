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

function ModUserModal(uid,umel,unam,ugvn,usts,upwd,uend) {
    $('#mum_imp_uid').val(uid);
    $('#mum_imp_mel').val(umel);
    $('#mum_imp_nam').val(unam);
    $('#mum_imp_gvn').val(ugvn);
    $('#mum_imp_pwd').val(upwd);
    $('#mum_sel_sts').val(usts);
    $('#mum_imp_end').val(uend);
    $('#ModalUserModify').modal();
}

function DelUserModal(uid,unam) {
    $('#mud_tit_unam').text(unam);
    $('#mud_imp_uid').val(uid);
    $('#ModalUserDelete').modal();
}


function MgtCreateModal(url) {

    $.get(url).done(function(data) {
	$('#mgtcremodal').html(data);
    }).fail(function(data) {
	$('#mgtcremodal').html("<h1> ERROR </h1>");
    })
    $('#ModalCreate').modal();
}

function MgtCreateDismiss(url) {

    $.get(url).done(function(data) {
	$('#mgtuncreate').html(data);
    }).fail(function(data) {
	$('#mgtuncreate').html("<h1> ERROR </h1>");
    })
    $('#ModalCreate').modal('hide');
}

function PayModal() {
    $('#ModalAccount').modal('hide');
    $('#ModalPay').modal();
}
