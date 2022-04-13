@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-primary btn-agregar"><i class="fa fa-plus"></i> Agregar</button> <br><br>
                <div class="table-responsive">
                    <table id="consulta" class="table">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Course</th>
                                <th>Name</th>
                                <th>Intro</th>
                                <th>Attempts</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar/Actualizar -->
    <form id="registro" autocomplete="off">
        <div class="modal fade" id="modal-registro" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">

                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="id" class="id">

                <div class="form-group">
                    <label>Courses</label>
                    <select name="course_id" id="course_id" class="form-control" required>
                        <option value="">Seleccionar</option>
                        @foreach($courses as $key  => $value)
                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="name form-control" required>
                </div>
                <div class="form-group">
                    <label>Intro</label>
                    <input type="text" name="intro" class="intro form-control" required>
                </div>
                <div class="form-group">
                    <label>Attempts</label>
                    <input type="number" min="1" name="attempts" class="attempts form-control" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary btn-submit">Save changes</button>
              </div>
            </div>
          </div>
        </div>
    </form>



@endsection

@section('scripts')

    <script>
        $('#consulta').DataTable({

            bAutoWidth: false,
            destroy:true,
            deferRender:true,
            bProcessing: true,
            iDisplayLength: 50,
            order:[[5,'desc'],[4,'asc']],
            language:{
                url:'{{ asset('js/spanish.json')  }}'
            },
            ajax:{
                url:'{{ route('home') }}',
                type:'GET'
            },
            columns:[
                { data:'id' },
                { data:'course_id' },
                { data:'name' },
                { data:'intro' },
                { data:'attempts' },
                { data:null,render:function(data){
                    return `
                        <button 
                        data-id = "${data.id}"
                        class="btn btn-primary btn-sm btn-edit"><i class="fa fa-pencil"></i></button>

                        <button 
                        data-id = "${data.id}"
                        class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></button>
                    `;
                }}
            ]
        });

    //Cargar Modal Agregar
    $(document).on('click','.btn-agregar',function(){
        $('#registro')[0].reset();
        $('.id').val('');
        $('.modal-title').html('Agregar Usuario');
        $('.btn-submit').html('Agregar');
        $('#modal-registro').modal('show');
    });


    //Cargar Modal Actualizar
    $(document).on('click','.btn-edit',function(){
        $('#registro')[0].reset();
        $('.id').val('');
        id =  $(this).data('id');
        var url_edit = '{{ route("quiz.edit", ":id") }}';
        url_edit     = url_edit.replace(':id', id);
        //Cargar Datos
        $.ajax({
            url:url_edit,
            type:'GET',
            data:{},
            dataType:'JSON',
            success:function(result){
                $('.name').val('').val( result.name );
                $('.intro').val('').val( result.intro );
                $('.attempts').val('').val( result.attempts );
                $('.id').val('').val( result.id );
                $('#course_id').val( result.course_id );
            }
        });
        $('.modal-title').html('Actualizar Usuario');
        $('.btn-submit').html('Actualizar');
        $('#modal-registro').modal('show');
    });

    //Registro
    $(document).on('submit','#registro',function(e){
        parametros = $(this).serialize();
        $.ajax({
            url:'{{ route('quiz.store') }}',
            type:'POST',
            data:parametros,
            dataType:'JSON',
            beforeSend:function(){
                Swal.fire({
                    title:'Cargando',
                    text :'Espere un momento...',
                    imageUrl:'{{ asset('img/loader.gif') }}',
                    showConfirmButton:false
                });
            },
            success:function(result){
                if( result.icon == 'success' ){
                    $('#consulta').DataTable().ajax.reload();
                    $('#modal-registro').modal('hide');
                }
                Swal.fire({
                    title : result.title,
                    text  : result.text,
                    icon  : result.icon,
                    timer : 3000,
                    showConfirmButton:false
                });
            }
        });
        e.preventDefault();
    });


    //Cargar Modal Eliminar
    $(document).on('click','.btn-delete',function(){
        id =  $(this).data('id');

        Swal.fire({
          title: 'Eliminar',
          text: "Está opción eliminará el registro de forma permanente",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Confirmar',
          cancelButtonText : 'Cancelar',
        }).then((result) => {
          if (result.isConfirmed) {
            var url_destroy = '{{ route("quiz.destroy", ":id") }}';
            url_destroy     = url_destroy.replace(':id', id);
            $.ajax({
                url:url_destroy,
                type:'DELETE',
                data:{'id':id,'_token':'{{ csrf_token() }}'},
                dataType:'JSON',
                beforeSend:function(){
                    Swal.fire({
                        title:'Cargando',
                        text :'Espere un momento...',
                        imageUrl:'{{ asset('img/loader.gif') }}',
                        showConfirmButton:false
                    });
                },
                success:function(result){
                if( result.icon == 'success' ){
                    $('#consulta').DataTable().ajax.reload();
                }
                Swal.fire({
                    title : result.title,
                    text  : result.text,
                    icon  : result.icon,
                    timer : 3000,
                    showConfirmButton:false
                });
                }
            });
          }
        });
        });

    </script>

@endsection