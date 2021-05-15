@if($atividade->secoes->count() == 0)

<div class="modal fade" id="modal_escolher_template" tabindex="-1" role="dialog" aria-labelledby="escolher_template_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="escolher_template_label">Escolher modelo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('salvarSecaoTemplate')}}" method="POST">
                    @csrf
                    <input type="hidden" value="{{$atividade->id}}" name="atividade_id" />
                    <div class="form-group">
                        <label for="instituicao">Instituição</label>
                        <select class="form-control" id="instituicao">
                            <option selected disabled>-- Selecione uma Instituição --</option>
                            @foreach(\App\Models\Instituicao::all() as $instituicao)
                            <option value="instituicao_{{$instituicao->id}}">{{$instituicao->nome}}</option>
                            @endforeach
                        </select>
                    </div>

                    @foreach(\App\Models\Instituicao::all() as $instituicao)
                    <div class="form-group templates_instituicoes" id="instituicao_{{$instituicao->id}}" style="display: none;">
                        <label for="template_{{$instituicao->id}}">Modelo</label>
                        <select class="form-control selects-template" id="template_{{$instituicao->id}}">
                            <option selected disabled>-- Selecione um modelo --</option>

                            @foreach($instituicao->templatesAtividade as $template)
                            <option dados_arvore="{{$template->arr_template}}" value="{{$template->id}}">{{$template->titulo}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach

                    <div id="arvore_secao_template" class="no_raiz" dados_arvore=""></div>

                    <script>
                     preparar_exibicao_escolha_templates();
                    </script>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-success">Criar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<p class="col">
    Nenhuma seção criada.
    <br>
    <a id="botao-criar-secao" data-toggle="modal" data-target="#modal-criar-secao" style="text-align:right; font-size: 15px; color: blue;" onclick="add_id_na_subsecao(null)">
        Clique aqui
    </a>
    para criar uma seção.
    <br>
    Ou <a href="#" id="botao-escolher-template" data-toggle="modal" data-target="#modal_escolher_template" style="text-align:right; font-size: 15px; color: blue;">Clique aqui</a> para escolher a partir de um modelo.
</p>
@endif
@foreach($atividade->secoes as $secao_loop)
@php echo $secao_loop->arvore_secoes($secao); @endphp
@endforeach
