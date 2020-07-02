$(function () {
  $(".modalAdd").on("click", function () {
    $("#judulModal").html("Tambah Data Mahasiswa");
    $("modal-footer button[type=submit]").html("Ubah Data");
  });

  $(".modalEdit").on("click", function () {
    $("#judulModal").html("Ubah Data Mahasiswa");
    $(".modal-footer button[type=submit]").html("Ubah Data");
    $(".modal-body form").attr(
      "action",
      "http://localhost/mvc/public/mahasiswa/edit"
    );

    const id = $(this).data("id");

    $.ajax({
      url: "http://localhost/ci-app/translator/getEdit",
      data: { id: id },
      method: "post",
      dataType: "json",
      success: function (data) {
        $("#nama").val(data.nama);
        $("#nim").val(data.nim);
        $("#email").val(data.email);
        $("#jurusan").val(data.jurusan);
        $("#id").val(data.id);
      },
    });
  });
});
