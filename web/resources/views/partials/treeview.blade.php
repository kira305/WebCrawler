<ul>
    @foreach ($children as $node)
      <li class="list-group-item">
        <a href="{{ $node['url'] }}">{{ $node['text'] }}</a>
        @if (!empty($node['children']))
          @include('partials.treeview', ['children' => $node['children']])
        @endif
      </li>
    @endforeach
</ul>
